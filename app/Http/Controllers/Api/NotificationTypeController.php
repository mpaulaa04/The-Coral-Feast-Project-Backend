<?php
/**
 * Class NotificationTypeController
 *
 * Handles API requests related to notification types.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationType;
use Illuminate\Http\JsonResponse;

class NotificationTypeController extends Controller
{
    /**
     * Display a listing of notification types.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $types = NotificationType::query()
            ->select('slug', 'name', 'default_title', 'background_color', 'text_color', 'border_color')
            ->orderBy('slug')
            ->get();

        return response()->json([
            'data' => $types,
        ]);
    }
}
