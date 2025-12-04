<?php
/**
 * Class UserMissionController
 *
 * Handles API requests related to mission progress, mission events,
 * and mission reward claims for a specific user.
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\TransactionType;
use App\Models\User;
use App\Models\UserMission;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserMissionController extends Controller
{

    /**
     * Retrieve a list of missions with progress and level information for the user.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function index(User $user): JsonResponse
    {
        $missions = Mission::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $data = $missions
            ->map(function (Mission $mission) use ($user) {
                $userMission = UserMission::firstOrCreate(
                    [
                        'mission_id' => $mission->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'progress' => 0,
                        'current_level' => 1,
                    ]
                );

                $this->ensureUserMissionState($mission, $userMission);

                if ($this->shouldHideMission($mission, $userMission)) {
                    return null;
                }

                return $this->formatMission($mission, $userMission);
            })
            ->filter()
            ->values();

        return response()->json(['data' => $data]);
    }

    /**
     * Record a mission-related event and update mission progress for the user.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function recordEvent(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'event' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'integer', 'min:1'],
        ]);

        $eventKey = $validated['event'];
        $amount = $validated['amount'] ?? 1;

        $missions = Mission::query()
            ->where('event_key', $eventKey)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($missions->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $updated = [];

        foreach ($missions as $mission) {
            $userMission = UserMission::firstOrCreate(
                [
                    'mission_id' => $mission->id,
                    'user_id' => $user->id,
                ],
                [
                    'progress' => 0,
                    'current_level' => 1,
                ]
            );

            $this->ensureUserMissionState($mission, $userMission);

            if ($this->shouldHideMission($mission, $userMission)) {
                continue;
            }

            if ($userMission->completed_at && $userMission->claimed_at === null) {
                $updated[] = $this->formatMission($mission, $userMission);
                continue;
            }

            if ($userMission->claimed_at && $userMission->current_level >= $this->maxLevelForMission($mission)) {
                continue;
            }

            $target = $this->levelTarget($mission, $userMission->current_level);

            $userMission->progress = min($target, $userMission->progress + $amount);

            if ($userMission->progress >= $target) {
                $userMission->progress = $target;
                $userMission->markCompleted();
            }

            $userMission->save();

            $updated[] = $this->formatMission($mission, $userMission);
        }

        return response()->json(['data' => $updated]);
    }
/**
     * Claim the reward for a completed mission level and update user progression.
     *
     * @param User $user
     * @param Mission $mission
     * @return JsonResponse
     */
    public function claim(User $user, Mission $mission): JsonResponse
    {
        $userMission = UserMission::firstOrCreate(
            [
                'mission_id' => $mission->id,
                'user_id' => $user->id,
            ],
            [
                'progress' => 0,
                'current_level' => 1,
            ]
        );

        $this->ensureUserMissionState($mission, $userMission);

        if (! $userMission->completed_at) {
            return response()->json([
                'message' => 'Esta misión aún no se completa.',
            ], 422);
        }

        $maxLevel = $this->maxLevelForMission($mission);
        $currentLevel = max(1, (int) $userMission->current_level);

        if ($userMission->claimed_at && $currentLevel >= $maxLevel) {
            return response()->json([
                'message' => 'Esta misión ya fue reclamada.',
            ], 422);
        }

        $wallet = $user->ensureWalletExists();
        $type = TransactionType::firstOrCreate(['transaction' => 'reward']);
        $reward = $this->levelReward($mission, $currentLevel);

        DB::transaction(function () use ($wallet, $type, $reward, $mission, $userMission, $currentLevel, $maxLevel): void {
            if ($reward > 0) {
                $wallet->increment('balance', $reward);

                $wallet->transactions()->create([
                    'transaction_type_id' => $type->id,
                    'amount' => $reward,
                    'event' => sprintf('Mission Level %d: %s', $currentLevel, $mission->description),
                ]);
            }

            if ($currentLevel < $maxLevel) {
                $userMission->current_level = $currentLevel + 1;
                $userMission->progress = 0;
                $userMission->completed_at = null;
                $userMission->claimed_at = null;
            } else {
                $userMission->markClaimed();
            }

            $userMission->save();
        });

        $wallet->refresh();
        $userMission->refresh();

        if ($this->shouldHideMission($mission, $userMission)) {
            return response()->json([
                'data' => null,
                'wallet' => [
                    'balance' => $wallet->balance,
                ],
            ]);
        }

        return response()->json([
            'data' => $this->formatMission($mission, $userMission),
            'wallet' => [
                'balance' => $wallet->balance,
            ],
        ]);
    }
/**
     * Format mission and user mission data into a consistent API response structure.
     *
     * @param Mission $mission
     * @param UserMission $userMission
     * @return array<string, mixed>
     */
    private function formatMission(Mission $mission, UserMission $userMission): array
    {
        $level = max(1, (int) $userMission->current_level);
        $maxLevel = $this->maxLevelForMission($mission);
        $target = $this->levelTarget($mission, $level);
        $reward = $this->levelReward($mission, $level);
        $progress = min($target, (int) $userMission->progress);

        return [
            'id' => $mission->id,
            'code' => $mission->code,
            'name' => $mission->name,
            'description' => $mission->description,
            'reward' => $reward,
            'event_key' => $mission->event_key,
            'target' => $target,
            'progress' => $progress,
            'completed' => $userMission->completed_at !== null,
            'claimed' => $userMission->claimed_at !== null,
            'reward_image' => $mission->reward_image_path,
            'is_repeatable' => (bool) $mission->is_repeatable,
            'current_level' => $level,
            'max_level' => $maxLevel,
            'sort_order' => (int) $mission->sort_order,
            'levels' => $mission->levels(),
        ];
    }
/**
     * Ensure the user's mission state is valid for the mission's configuration.
     *
     * @param Mission $mission
     * @param UserMission $userMission
     * @return void
     */
    private function ensureUserMissionState(Mission $mission, UserMission $userMission): void
    {
        $maxLevel = $this->maxLevelForMission($mission);
        $currentLevel = (int) ($userMission->current_level ?: 1);

        if ($currentLevel < 1) {
            $currentLevel = 1;
        }

        if ($currentLevel > $maxLevel) {
            $currentLevel = $maxLevel;
        }

        $userMission->current_level = $currentLevel;

        if ($this->shouldResetForNewDay($mission, $userMission, $maxLevel)) {
            $userMission->current_level = 1;
            $userMission->progress = 0;
            $userMission->completed_at = null;
            $userMission->claimed_at = null;
        }

        $target = $this->levelTarget($mission, $userMission->current_level);

        if ($userMission->progress > $target) {
            $userMission->progress = $target;
        }

        if ($userMission->progress < $target && $userMission->completed_at !== null && $userMission->claimed_at === null) {
            $userMission->completed_at = null;
        }

        if ($userMission->isDirty()) {
            $userMission->save();
        }
    }
/**
     * Determine if a mission should be reset based on daily rules.
     *
     * @param Mission $mission
     * @param UserMission $userMission
     * @param int $maxLevel
     * @return bool
     */
    private function shouldResetForNewDay(Mission $mission, UserMission $userMission, int $maxLevel): bool
    {
        if ($userMission->current_level < $maxLevel) {
            return false;
        }

        if ($userMission->claimed_at === null) {
            return false;
        }

        $claimedAt = Carbon::parse($userMission->claimed_at);

        return $claimedAt->lt(Carbon::now()->startOfDay());
    }
/**
     * Determine if a mission should be hidden for the user.
     *
     * @param Mission $mission
     * @param UserMission $userMission
     * @return bool
     */
    private function shouldHideMission(Mission $mission, UserMission $userMission): bool
    {
        return $userMission->current_level >= $this->maxLevelForMission($mission)
            && $userMission->claimed_at !== null;
    }
/**
     * Get the maximum number of levels available for a mission.
     *
     * @param Mission $mission
     * @return int
     */
    private function maxLevelForMission(Mission $mission): int
    {
        $levels = $mission->levels();

        if (! empty($levels)) {
            return count($levels);
        }

        return 5;
    }
/**
     * Retrieve level configuration details for a specific level of a mission.
     *
     * @param Mission $mission
     * @param int $level
     * @return array<string, mixed>
     */
    private function levelConfig(Mission $mission, int $level): array
    {
        $levels = $mission->levels();
        $index = $level - 1;

        if (isset($levels[$index]) && is_array($levels[$index])) {
            return $levels[$index];
        }

        if (! empty($levels)) {
            $last = $levels[count($levels) - 1];

            return is_array($last) ? $last : [];
        }

        return [
            'target' => $mission->target_amount,
            'reward' => $mission->reward,
        ];
    }
/**
     * Determine the target amount required to complete a mission level.
     *
     * @param Mission $mission
     * @param int $level
     * @return int
     */
    private function levelTarget(Mission $mission, int $level): int
    {
        $config = $this->levelConfig($mission, max(1, $level));

        return max(1, (int) ($config['target'] ?? $mission->target_amount ?? 1));
    }

    /**
     * Determine the reward amount granted for a mission level.
     *
     * @param Mission $mission
     * @param int $level
     * @return int
     */
    private function levelReward(Mission $mission, int $level): int
    {
        $config = $this->levelConfig($mission, max(1, $level));

        return max(0, (int) ($config['reward'] ?? $mission->reward ?? 0));
    }
}
