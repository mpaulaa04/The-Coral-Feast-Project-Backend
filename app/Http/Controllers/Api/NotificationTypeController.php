<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationType;
use Illuminate\Http\JsonResponse;

class NotificationTypeController extends Controller
{
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
