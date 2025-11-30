<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(User $user, Request $request): JsonResponse
    {
        $limit = (int) $request->integer('limit', 10);
        $limit = $limit > 0 ? min($limit, 50) : 10;

        $notifications = Notification::query()
            ->with('type')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        $unreadCount = Notification::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $payload = $notifications
            ->map(fn (Notification $notification) => $this->transformNotification($notification))
            ->values();

        return response()->json([
            'data' => $payload,
            'meta' => [
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    public function store(User $user, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'exists:notification_types,slug'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $type = null;

        if (! empty($validated['type'])) {
            $type = NotificationType::query()
                ->where('slug', $validated['type'])
                ->first();
        }

        if (! $type) {
            $type = NotificationType::query()
                ->where('slug', 'default')
                ->first();

            if (! $type) {
                $type = NotificationType::query()->firstOrCreate(
                    ['slug' => 'default'],
                    [
                        'name' => 'General',
                        'default_title' => 'Aviso',
                    ]
                );
            }
        }

        $notification = Notification::query()->create([
            'notification_type_id' => $type?->id,
            'user_id' => $user->id,
            'title' => $validated['title'] ?? $type?->default_title ?? 'Aviso',
            'content' => $validated['content'],
            'is_read' => false,
        ]);

        $notification->load('type');

        return response()->json([
            'data' => $this->transformNotification($notification),
        ], 201);
    }

    public function markAllRead(User $user): JsonResponse
    {
        Notification::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'status' => 'ok',
        ]);
    }

    protected function transformNotification(Notification $notification): array
    {
        $notification->loadMissing('type');

        $type = $notification->type;

        return [
            'id' => $notification->id,
            'type' => $type ? [
                'slug' => $type->slug,
                'name' => $type->name,
                'default_title' => $type->default_title,
                'background_color' => $type->background_color,
                'text_color' => $type->text_color,
                'border_color' => $type->border_color,
            ] : null,
            'title' => $notification->title,
            'content' => $notification->content,
            'is_read' => (bool) $notification->is_read,
            'created_at' => $this->formatTimestamp($notification->created_at),
            'read_at' => $this->formatTimestamp($notification->read_at),
            'is_fresh' => $notification->isFresh(),
        ];
    }

    protected function formatTimestamp($value): ?string
    {
        if (! $value instanceof CarbonInterface) {
            return null;
        }

        return $value->toIso8601String();
    }
}
