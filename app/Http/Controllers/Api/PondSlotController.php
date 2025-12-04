<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\SerializesPonds;
use App\Http\Controllers\Controller;
use App\Models\Fish;
use App\Models\Plant;
use App\Models\Pond;
use App\Models\PondSlot;
use App\Models\PondSlotStatus;
use App\Models\Supplement;
use App\Models\ToolUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PondSlotController extends Controller
{
    use SerializesPonds;

    private const MAX_PLANT_EFFECT_DURATION_SECONDS = 30;

    private const STATUS_FLOW = ['empty', 'egg', 'juvenile', 'adult', 'dead'];

    /**
     * @var array<string, int>
     */
    private array $statusCache = [];

    public function index(Request $request, Pond $pond): JsonResponse
    {
        $this->assertUserAccess($request, $pond);

        $slots = $pond->slots()->with(['fish', 'status'])->orderBy('id')->get();

        return response()->json([
            'data' => $slots->map(fn (PondSlot $slot) => $this->serializeSlot($slot))->values(),
        ]);
    }

    public function stock(Request $request, Pond $pond, PondSlot $slot): JsonResponse
    {
        $this->assertSlotAccess($request, $pond, $slot);

        $data = $request->validate([
            'fish_id' => ['required', 'exists:fish,id'],
        ]);

        $slot->loadMissing('status');

        $currentStatus = $slot->status?->name ?? 'empty';

        if (! in_array($currentStatus, ['empty', 'dead'], true)) {
            return response()->json([
                'message' => 'The slot must be empty before stocking a new fish.',
            ], 422);
        }

        $fish = Fish::findOrFail($data['fish_id']);

        $slot->forceFill([
            'fish_id' => $fish->id,
            'status_id' => $this->statusIdFor('egg'),
            'health' => 100,
            'oxygen_level' => 100,
            'ph_level' => 100,
            'feeding_count' => 0,
            'feeding_limit' => $fish->feedings_per_day,
            'has_ph_issue' => false,
            'has_oxygen_issue' => false,
            'has_temperature_issue' => false,
            'has_water_quality_issue' => false,
            'stage_started_at' => now(),
            'stage_progress_seconds' => 0,
            'stage_duration_seconds' => $fish->egg_stage_seconds,
            'last_fed_at' => null,
            'last_oxygenated_at' => null,
            'last_ph_adjusted_at' => null,
            'last_cleaned_at' => now(),
        ])->save();

        return response()->json([
            'message' => 'Fish stocked successfully.',
            'data' => $this->serializeSlot($slot->fresh()->load(['fish', 'status', 'plant'])),
        ], 201);
    }

    public function feed(Request $request, Pond $pond, PondSlot $slot): JsonResponse
    {
        $this->assertSlotAccess($request, $pond, $slot);

        $slot->loadMissing(['status', 'fish']);

        if (! $slot->fish || in_array($slot->status?->name, ['empty', 'dead'], true)) {
            return response()->json([
                'message' => 'No living fish to feed in this slot.',
            ], 422);
        }

        if ($slot->feeding_count >= $slot->feeding_limit) {
            return response()->json([
                'message' => 'Feeding limit reached for this slot.',
            ], 422);
        }

        $slot->feeding_count += 1;
        $slot->health = min(100, $slot->health + 5);
        $slot->last_fed_at = now();
        $slot->save();

        return response()->json([
            'message' => 'Fish fed successfully.',
            'data' => $this->serializeSlot($slot->fresh()->load(['fish', 'status', 'plant'])),
        ]);
    }

    public function plant(Request $request, Pond $pond, PondSlot $slot): JsonResponse
    {
        $this->assertSlotAccess($request, $pond, $slot);

        $data = $request->validate([
            'plant_id' => ['required', 'exists:plants,id'],
        ]);

        $slot->loadMissing(['status', 'fish']);

        $statusName = strtolower($slot->status?->name ?? 'empty');

        if (! $slot->fish || in_array($statusName, ['empty', 'dead'], true)) {
            return response()->json([
                'message' => 'Necesitas un pez vivo en este espacio antes de plantar.',
            ], 422);
        }

        if ($statusName === 'egg') {
            return response()->json([
                'message' => 'No puedes aplicar plantas sobre un huevo. Espera a que nazca el pez.',
            ], 422);
        }

        $plant = Plant::findOrFail($data['plant_id']);

        $metadata = (array) $plant->metadata;
        $effects = (array) ($metadata['effects'] ?? []);

        $maxLifetime = max(0, (int) $this->plantEffectMaxLifetimeSeconds());
        $rawLifetime = (int) ($effects['lifetime_seconds'] ?? 0);
        $effectiveLifetime = $rawLifetime > 0
            ? min($rawLifetime, $maxLifetime)
            : $maxLifetime;

        $now = now();
        $expiresAt = $effectiveLifetime > 0
            ? (clone $now)->addSeconds($effectiveLifetime)
            : null;

        if ($effectiveLifetime > 0) {
            $effects['lifetime_seconds'] = $effectiveLifetime;
        } else {
            unset($effects['lifetime_seconds']);
        }

        $slot->forceFill([
            'plant_id' => $plant->id,
            'plant_placed_at' => $now,
            'plant_effect_state' => ! empty($effects) ? $effects : null,
            'plant_effect_expires_at' => $expiresAt,
        ])->save();

        $slot->refresh();

        $updates = [];

        if ($plant->oxygen_bonus > 0) {
            $updates['oxygen_level'] = min(100, (int) $slot->oxygen_level + (int) $plant->oxygen_bonus);
        }

        if ($plant->ph_bonus > 0) {
            $updates['ph_level'] = min(100, (int) $slot->ph_level + (int) $plant->ph_bonus);
        }

        if ($plant->health_regeneration > 0 && $slot->health > 0) {
            $updates['health'] = min(100, (int) $slot->health + (int) $plant->health_regeneration);
        }

        if ($updates) {
            $slot->forceFill($updates)->save();
            $slot->refresh();
        }

        return response()->json([
            'message' => 'Plant added to pond slot successfully.',
            'data' => $this->serializeSlot($slot->load(['fish', 'status', 'plant'])),
        ]);
    }

    public function supplement(Request $request, Pond $pond, PondSlot $slot): JsonResponse
    {
        $this->assertSlotAccess($request, $pond, $slot);

        $data = $request->validate([
            'supplement_id' => ['required', 'exists:supplements,id'],
        ]);

        $slot->loadMissing(['status', 'fish']);

        if (! $slot->fish || in_array($slot->status?->name, ['empty', 'dead'], true)) {
            return response()->json([
                'message' => 'No living fish to supplement in this slot.',
            ], 422);
        }

        $supplement = Supplement::findOrFail($data['supplement_id']);

        $updatedHealth = min(100, (int) $slot->health + (int) $supplement->health_boost);

        $updates = [
            'health' => $updatedHealth,
            'last_fed_at' => now(),
        ];

        if ($supplement->hunger_reset) {
            $updates['feeding_count'] = 0;
        }

        if ($supplement->feeding_limit_bonus > 0) {
            $updates['feeding_limit'] = min(10, (int) $slot->feeding_limit + (int) $supplement->feeding_limit_bonus);
        }

        $slot->forceFill($updates)->save();

        $slot->refresh();

        return response()->json([
            'message' => 'Supplement applied successfully.',
            'data' => $this->serializeSlot($slot->load(['fish', 'status', 'plant'])),
        ]);
    }

    public function clean(Request $request, Pond $pond, PondSlot $slot): JsonResponse
    {
        $this->assertSlotAccess($request, $pond, $slot);

        $slot->forceFill([
            'has_water_quality_issue' => false,
            'last_cleaned_at' => now(),
        ])->save();

        return response()->json([
            'message' => 'Pond slot cleaned successfully.',
            'data' => $this->serializeSlot($slot->fresh()->load(['fish', 'status', 'plant'])),
        ]);
    }

    public function advance(Request $request, Pond $pond, PondSlot $slot): JsonResponse
    {
        $this->assertSlotAccess($request, $pond, $slot);

        $slot->loadMissing(['status', 'fish']);

        $target = $request->input('target_status');

        if ($target) {
            $target = strtolower((string) $target);

            if (! in_array($target, self::STATUS_FLOW, true)) {
                return response()->json([
                    'message' => 'Unknown target status provided.',
                ], 422);
            }
        } else {
            $current = $slot->status?->name ?? 'empty';
            $target = $this->nextStatus($current);

            if (! $target) {
                return response()->json([
                    'message' => 'Slot is already at the final stage.',
                ], 422);
            }
        }

        if ($target !== 'empty' && ! $slot->fish_id) {
            return response()->json([
                'message' => 'Cannot change stage for a slot without a fish.',
            ], 422);
        }

        $updates = [
            'status_id' => $this->statusIdFor($target),
        ];

        if ($target === 'empty') {
            $updates = array_merge($updates, [
                'fish_id' => null,
                'health' => 100,
                'oxygen_level' => 100,
                'ph_level' => 100,
                'feeding_count' => 0,
                'has_ph_issue' => false,
                'has_oxygen_issue' => false,
                'has_temperature_issue' => false,
                'has_water_quality_issue' => false,
                'stage_started_at' => null,
                'stage_progress_seconds' => 0,
                'stage_duration_seconds' => 0,
                'last_fed_at' => null,
                'last_oxygenated_at' => null,
                'last_ph_adjusted_at' => null,
                'last_cleaned_at' => null,
                'plant_id' => null,
                'plant_placed_at' => null,
                'plant_effect_state' => null,
                'plant_effect_expires_at' => null,
            ]);
        } elseif ($target === 'dead') {
            $updates = array_merge($updates, [
                'health' => 0,
                'stage_started_at' => now(),
                'stage_progress_seconds' => 0,
                'stage_duration_seconds' => 0,
                'plant_id' => null,
                'plant_placed_at' => null,
                'plant_effect_state' => null,
                'plant_effect_expires_at' => null,
            ]);
        } else {
            $updates['stage_started_at'] = now();
            $updates['stage_progress_seconds'] = 0;
            $updates['stage_duration_seconds'] = $this->stageDurationFor($slot, $target);
        }

        $slot->forceFill($updates)->save();

        return response()->json([
            'message' => 'Pond slot stage updated successfully.',
            'data' => $this->serializeSlot($slot->fresh()->load(['fish', 'status', 'plant'])),
        ]);
    }

    public function harvest(Request $request, Pond $pond, PondSlot $slot): JsonResponse
    {
        $this->assertSlotAccess($request, $pond, $slot);

        $slot->loadMissing('status');

        if ($slot->status?->name !== 'adult') {
            return response()->json([
                'message' => 'Only adult fish can be harvested.',
            ], 422);
        }

        $slot->forceFill([
            'status_id' => $this->statusIdFor('empty'),
            'fish_id' => null,
            'health' => 100,
            'oxygen_level' => 100,
            'ph_level' => 100,
            'feeding_count' => 0,
            'has_ph_issue' => false,
            'has_oxygen_issue' => false,
            'has_temperature_issue' => false,
            'has_water_quality_issue' => false,
            'stage_started_at' => null,
            'stage_progress_seconds' => 0,
            'stage_duration_seconds' => 0,
            'last_fed_at' => null,
            'last_oxygenated_at' => null,
            'last_ph_adjusted_at' => null,
            'last_cleaned_at' => now(),
        ])->save();

        return response()->json([
            'message' => 'Fish harvested successfully.',
            'data' => $this->serializeSlot($slot->fresh()->load(['fish', 'status', 'plant'])),
        ]);
    }

    public function markDead(Request $request, Pond $pond, PondSlot $slot): JsonResponse
    {
        $this->assertSlotAccess($request, $pond, $slot);

        if (! $slot->fish_id) {
            return response()->json([
                'message' => 'Cannot mark an empty slot as dead.',
            ], 422);
        }

        $slot->forceFill([
            'status_id' => $this->statusIdFor('dead'),
            'health' => 0,
            'stage_started_at' => now(),
            'stage_progress_seconds' => 0,
            'stage_duration_seconds' => 0,
            'plant_id' => null,
            'plant_placed_at' => null,
            'plant_effect_state' => null,
            'plant_effect_expires_at' => null,
        ])->save();

        return response()->json([
            'message' => 'Pond slot marked as dead.',
            'data' => $this->serializeSlot($slot->fresh()->load(['fish', 'status', 'plant'])),
        ]);
    }

    public function raiseIssue(Request $request, Pond $pond, PondSlot $slot, string $issue): JsonResponse
    {
        $this->assertSlotAccess($request, $pond, $slot);

        $normalized = str_replace('_', '-', strtolower($issue));

        $map = [
            'ph' => [
                'flag' => 'has_ph_issue',
                'level' => 'ph_level',
                'last' => 'last_ph_adjusted_at',
                'decrement' => 20,
            ],
            'oxygen' => [
                'flag' => 'has_oxygen_issue',
                'level' => 'oxygen_level',
                'last' => 'last_oxygenated_at',
                'decrement' => 25,
            ],
            'temperature' => [
                'flag' => 'has_temperature_issue',
                'level' => null,
                'last' => null,
                'decrement' => null,
            ],
            'water-quality' => [
                'flag' => 'has_water_quality_issue',
                'level' => null,
                'last' => 'last_cleaned_at',
                'decrement' => null,
            ],
        ];

        if (! isset($map[$normalized])) {
            return response()->json([
                'message' => 'Unknown issue type.',
            ], 422);
        }

        $config = $map[$normalized];

        $updates = [
            $config['flag'] => true,
        ];

        if ($config['level']) {
            $currentLevel = (int) $slot->{$config['level']};
            $updates[$config['level']] = max(0, $currentLevel - (int) $config['decrement']);
        }

        if ($config['last']) {
            $updates[$config['last']] = null;
        }

        $slot->forceFill($updates)->save();

        return response()->json([
            'message' => 'Issue applied successfully.',
            'data' => $this->serializeSlot($slot->fresh()->load(['fish', 'status', 'plant'])),
        ]);
    }

    public function resolveIssue(Request $request, Pond $pond, PondSlot $slot, string $issue): JsonResponse
    {
        $this->assertSlotAccess($request, $pond, $slot);

        $normalized = str_replace('_', '-', strtolower($issue));

        $map = [
            'ph' => [
                'flag' => 'has_ph_issue',
                'level' => 'ph_level',
                'level_reset' => 100,
                'last' => 'last_ph_adjusted_at',
                'message' => 'pH issue resolved successfully.',
            ],
            'oxygen' => [
                'flag' => 'has_oxygen_issue',
                'level' => 'oxygen_level',
                'level_reset' => 100,
                'last' => 'last_oxygenated_at',
                'message' => 'Oxygen issue resolved successfully.',
            ],
            'temperature' => [
                'flag' => 'has_temperature_issue',
                'level' => null,
                'level_reset' => null,
                'last' => null,
                'message' => 'Temperature issue resolved successfully.',
            ],
            'water-quality' => [
                'flag' => 'has_water_quality_issue',
                'level' => null,
                'level_reset' => null,
                'last' => 'last_cleaned_at',
                'message' => 'Water quality restored successfully.',
            ],
        ];

        if (! isset($map[$normalized])) {
            return response()->json([
                'message' => 'Unknown issue type.',
            ], 422);
        }

        $config = $map[$normalized];

        $updates = [
            $config['flag'] => false,
        ];

        if ($config['level'] && $config['level_reset'] !== null) {
            $updates[$config['level']] = (int) $config['level_reset'];
        }

        if ($config['last']) {
            $updates[$config['last']] = now();
        }

        $slot->forceFill($updates)->save();

        $this->registerToolUsage($pond->user_id, $normalized);

        return response()->json([
            'message' => $config['message'],
            'data' => $this->serializeSlot($slot->fresh()->load(['fish', 'status', 'plant'])),
        ]);
    }

    private function assertSlotAccess(Request $request, Pond $pond, PondSlot $slot): void
    {
        $this->assertUserAccess($request, $pond);

        if ($slot->pond_id !== $pond->id) {
            abort(404, 'The requested slot does not belong to this pond.');
        }
    }

    private function assertUserAccess(Request $request, Pond $pond): void
    {
        $userId = $this->requireUserId($request);

        if ($userId !== $pond->user_id) {
            abort(403, 'The provided user does not own this pond.');
        }
    }

    private function statusIdFor(string $name): int
    {
        if (! isset($this->statusCache[$name])) {
            $id = PondSlotStatus::where('name', $name)->value('id');

            if (! $id) {
                abort(500, sprintf('Missing pond slot status "%s". Run the seeders.', $name));
            }

            $this->statusCache[$name] = $id;
        }

        return $this->statusCache[$name];
    }

    private function nextStatus(string $current): ?string
    {
        $index = array_search($current, self::STATUS_FLOW, true);

        if ($index === false || $index === count(self::STATUS_FLOW) - 1) {
            return null;
        }

        return self::STATUS_FLOW[$index + 1];
    }

    private function requireUserId(Request $request): int
    {
        $userId = (int) $request->input('user_id');

        if ($userId <= 0) {
            abort(422, 'A valid user_id is required for this request.');
        }

        return $userId;
    }

    private function registerToolUsage(int $userId, string $issue): void
    {
        $slugMap = [
            'ph' => 'ph',
            'oxygen' => 'oxygen',
            'temperature' => 'temperature',
            'water-quality' => 'water_quality',
        ];

        $slug = $slugMap[$issue] ?? null;

        if (! $slug || ! isset(ToolUsage::SUPPORTED_TOOLS[$slug])) {
            return;
        }

        $usage = ToolUsage::firstOrNew([
            'user_id' => $userId,
            'tool_slug' => $slug,
        ]);

        $usage->usage_count = max(0, (int) $usage->usage_count) + 1;
        $usage->last_used_at = now();
        $usage->save();
    }

    private function stageDurationFor(PondSlot $slot, string $status): int
    {
        $slot->loadMissing('fish');

        $fish = $slot->fish;

        if (! $fish) {
            return 0;
        }

        return match ($status) {
            'egg' => (int) $fish->egg_stage_seconds,
            'juvenile' => (int) $fish->juvenile_stage_seconds,
            'adult' => (int) $fish->adult_stage_seconds,
            default => 0,
        };
    }
}
