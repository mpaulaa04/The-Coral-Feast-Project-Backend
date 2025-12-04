<?php
/**
 * Class PondSlotStatusController
 *
 * Handles API requests related to pond slot statuses.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PondSlotStatus;
use Illuminate\Http\JsonResponse;

class PondSlotStatusController extends Controller
{
    /**
     * Display a listing of pond slot statuses.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $statuses = PondSlotStatus::query()
            ->orderBy('id')
            ->get(['id', 'name', 'created_at', 'updated_at'])
            ->map(fn (PondSlotStatus $status) => [
                'id' => $status->id,
                'name' => $status->name,
                'created_at' => optional($status->created_at)->toIso8601String(),
                'updated_at' => optional($status->updated_at)->toIso8601String(),
            ])
            ->values();

        return response()->json(['data' => $statuses]);
    }
}
