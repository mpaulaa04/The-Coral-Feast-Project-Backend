<?php
/**
 * Class StatsController
 *
 * Handles API requests related to user statistics and leaderboard data.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PondSlotStatus;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Display a listing of user statistics and leaderboard data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $activeStatusIds = PondSlotStatus::query()
            ->whereNotIn('name', ['empty', 'dead'])
            ->pluck('id')
            ->all();

        $users = User::query()
            ->select(['users.id', 'users.name', 'users.days_played', 'users.last_played_at'])
            ->selectSub(function ($query) use ($activeStatusIds) {
                $query->from('pond_slots')
                    ->join('ponds', 'pond_slots.pond_id', '=', 'ponds.id')
                    ->whereColumn('ponds.user_id', 'users.id')
                    ->whereNotNull('pond_slots.fish_id');

                if (! empty($activeStatusIds)) {
                    $query->whereIn('pond_slots.status_id', $activeStatusIds);
                }

                $query->selectRaw('COUNT(*)');
            }, 'fish_count')
            ->selectSub(function ($query) {
                $query->from('wallets')
                    ->select('balance')
                    ->whereColumn('wallets.user_id', 'users.id')
                    ->limit(1);
            }, 'wallet_balance')
            ->orderBy('users.name')
            ->get()
            ->map(function (User $user) {
                $lastPlayed = $user->last_played_at?->toIso8601String();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'days_played' => (int) ($user->days_played ?? 0),
                    'last_played_at' => $lastPlayed,
                    'fish_count' => (int) ($user->fish_count ?? 0),
                    'wallet_balance' => (int) ($user->wallet_balance ?? 0),
                    'avatar_url' => null,
                ];
            });

        return response()->json([
            'data' => $users,
        ]);
    }
}
