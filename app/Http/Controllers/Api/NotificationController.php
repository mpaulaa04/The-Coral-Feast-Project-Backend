<?php
/**
 * Class NotificationController
 *
 * Handles API requests related to user notifications.
 */

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
    private const TYPE_PRESETS = [
        'default' => [
            'slug' => 'default',
            'name' => 'General',
            'default_title' => 'Aviso',
            'background_color' => '#1A365D',
            'text_color' => '#FFFFFF',
            'border_color' => '#2A4365',
        ],
        'success' => [
            'slug' => 'success',
            'name' => 'Correcto',
            'default_title' => 'Todo listo',
            'background_color' => '#2F855A',
            'text_color' => '#FFFFFF',
            'border_color' => '#22543D',
        ],
        'error' => [
            'slug' => 'error',
            'name' => 'Error',
            'default_title' => 'Ocurrió un problema',
            'background_color' => '#C53030',
            'text_color' => '#FFFFFF',
            'border_color' => '#822727',
        ],
        'warning' => [
            'slug' => 'warning',
            'name' => 'Alerta',
            'default_title' => 'Atención',
            'background_color' => '#D69E2E',
            'text_color' => '#1A202C',
            'border_color' => '#B7791F',
        ],
        'market' => [
            'slug' => 'market',
            'name' => 'Oferta de mercado',
            'default_title' => 'Oferta especial',
            'background_color' => '#6B46C1',
            'text_color' => '#FFFFFF',
            'border_color' => '#553C9A',
        ],
    ];

    private const DEFAULT_TYPE_SLUG = 'default';

    /**
     * Display a listing of notifications for a user.
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Store a newly created notification for a user.
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function store(User $user, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $type = null;
        $typeSlug = null;

        if (! empty($validated['type'])) {
            $typeSlug = strtolower(trim($validated['type']));

            if ($typeSlug !== '') {
                $type = NotificationType::query()
                    ->where('slug', $typeSlug)
                    ->first();

                if (! $type && array_key_exists($typeSlug, self::TYPE_PRESETS)) {
                    $preset = self::TYPE_PRESETS[$typeSlug];

                    $type = NotificationType::query()->firstOrCreate(
                        ['slug' => $preset['slug']],
                        [
                            'name' => $preset['name'],
                            'default_title' => $preset['default_title'],
                            'background_color' => $preset['background_color'],
                            'text_color' => $preset['text_color'],
                            'border_color' => $preset['border_color'],
                        ]
                    );
                }
            }
        }

        if (! $type) {
            $fallback = $this->presetFor(self::DEFAULT_TYPE_SLUG);

            $type = NotificationType::query()->firstOrCreate(
                ['slug' => $fallback['slug']],
                [
                    'name' => $fallback['name'],
                    'default_title' => $fallback['default_title'],
                    'background_color' => $fallback['background_color'],
                    'text_color' => $fallback['text_color'],
                    'border_color' => $fallback['border_color'],
                ]
            );
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

    /**
     * Mark all notifications as read for a user.
     *
     * @param User $user
     * @return JsonResponse
     */
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

        $preset = $this->presetFor($type?->slug ?? self::DEFAULT_TYPE_SLUG);

        $typePayload = [
            'slug' => $type?->slug ?? $preset['slug'],
            'name' => $type?->name ?? $preset['name'],
            'default_title' => $type?->default_title ?? $preset['default_title'],
            'background_color' => $type?->background_color ?? $preset['background_color'],
            'text_color' => $type?->text_color ?? $preset['text_color'],
            'border_color' => $type?->border_color ?? $preset['border_color'],
        ];

        return [
            'id' => $notification->id,
            'type' => $typePayload,
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

    protected function presetFor(string $slug): array
    {
        $slug = strtolower($slug);

        return self::TYPE_PRESETS[$slug] ?? self::TYPE_PRESETS[self::DEFAULT_TYPE_SLUG];
    }
}
