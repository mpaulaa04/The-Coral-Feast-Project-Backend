<?php
/**
 * Class NotificationType
 *
 * Represents a type of notification in the system.
 *
 * @property int $id
 * @property string $slug Unique slug for the notification type
 * @property string $name Name of the notification type
 * @property string $default_title Default title for notifications of this type
 * @property string $background_color Background color for notifications of this type
 * @property string $text_color Text color for notifications of this type
 * @property string $border_color Border color for notifications of this type
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationType whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationType whereName($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'default_title',
        'background_color',
        'text_color',
        'border_color',
    ];

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
