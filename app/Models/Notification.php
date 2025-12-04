<?php
/**
 * Class Notification
 *
 * Represents a notification for a user in the system.
 *
 * @property int $id
 * @property int $notification_type_id Type of the notification
 * @property int $user_id ID of the user who receives the notification
 * @property string $title Title of the notification
 * @property string $content Content of the notification
 * @property bool $is_read Indicates if the notification has been read
 * @property CarbonInterface|null $read_at Date and time when the notification was read
 * @property CarbonInterface $created_at Date and time when the notification was created
 * @property CarbonInterface $updated_at Date and time when the notification was last updated
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereNotificationTypeId($value)
 */

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_type_id',
        'user_id',
        'title',
        'content',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class, 'notification_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->forceFill([
                'is_read' => true,
                'read_at' => now(),
            ])->save();
        }
    }

    public function isFresh(): bool
    {
        $createdAt = $this->created_at;

        if (! $createdAt instanceof CarbonInterface) {
            return false;
        }

        return $createdAt->diffInMinutes(now()) < 5;
    }
}
