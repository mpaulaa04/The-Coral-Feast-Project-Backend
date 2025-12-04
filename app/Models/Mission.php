<?php
/**
 * Class Mission
 *
 * Represents a mission in the system.
 *
 * @property int $id
 * @property string $code Unique code for the mission
 * @property string $name Name of the mission
 * @property string $description Description of the mission
 * @property string $event_key Event key associated with the mission
 * @property int $target_amount Target amount to complete the mission
 * @property int $reward Reward for completing the mission
 * @property bool $is_repeatable Indicates if the mission can be repeated
 * @property int $sort_order Sort order for display
 * @property string $reward_image_path Path to the reward image
 * @property array $metadata Additional metadata
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereName($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mission extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'event_key',
        'target_amount',
        'reward',
        'is_repeatable',
        'sort_order',
        'reward_image_path',
        'metadata',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'target_amount' => 'integer',
        'reward' => 'integer',
        'is_repeatable' => 'boolean',
        'metadata' => 'array',
    ];

    public function userMissions(): HasMany
    {
        return $this->hasMany(UserMission::class);
    }

    public function levels(): array
    {
        $levels = $this->metadata['levels'] ?? null;

        if (! is_array($levels) || empty($levels)) {
            return [];
        }

        return array_values(array_filter($levels, static function ($level): bool {
            return is_array($level);
        }));
    }
}
