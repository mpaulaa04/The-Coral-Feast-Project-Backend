<?php
/**
 * Class ToolUsageController
 *
 * Handles API requests related to user tool usage statistics.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ToolUsage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ToolUsageController extends Controller
{
    /**
     * Display a listing of tool usage statistics for a user.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function index(User $user): JsonResponse
    {
        $records = $user->toolUsages()
            ->select(['tool_slug', 'usage_count', 'last_used_at'])
            ->get()
            ->keyBy('tool_slug');

        $data = [];

        foreach (ToolUsage::SUPPORTED_TOOLS as $slug => $meta) {
            $record = $records->get($slug);

            $data[] = [
                'tool_slug' => $slug,
                'label' => $meta['label'] ?? ucfirst(str_replace('_', ' ', $slug)),
                'usage_count' => $record?->usage_count ?? 0,
                'last_used_at' => $record?->last_used_at?->toISOString(),
            ];
        }

        return response()->json(['data' => $data]);
    }

    /**
     * Store or update tool usage statistics for a user.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function store(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'tool_slug' => 'required|string|in:' . implode(',', array_keys(ToolUsage::SUPPORTED_TOOLS)),
            'amount' => 'nullable|integer|min:1|max:1000',
        ]);

        $amount = $validated['amount'] ?? 1;
        $slug = $validated['tool_slug'];

        $usage = $user->toolUsages()->firstOrNew(['tool_slug' => $slug]);
        $usage->usage_count = max(0, (int) $usage->usage_count) + $amount;
        $usage->last_used_at = now();
        $usage->save();

        $meta = ToolUsage::SUPPORTED_TOOLS[$slug] ?? [];

        return response()->json([
            'data' => [
                'tool_slug' => $slug,
                'label' => $meta['label'] ?? ucfirst(str_replace('_', ' ', $slug)),
                'usage_count' => (int) $usage->usage_count,
                'last_used_at' => $usage->last_used_at?->toISOString(),
            ],
        ]);
    }
}
