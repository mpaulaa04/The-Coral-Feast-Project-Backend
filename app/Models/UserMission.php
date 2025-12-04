<?php
/**
 * Class UserMission
 *
 * Represents a user's progress and state for a specific mission.
 *
 * @property int $id
 * @property int $mission_id ID of the associated mission
 * @property int $user_id ID of the user
 * @property int $progress Progress value for the mission
 * @property int $current_level Current level in the mission
 * @property \Illuminate\Support\Carbon|null $completed_at Date and time when the mission was completed
 * @property \Illuminate\Support\Carbon|null $claimed_at Date and time when the reward was claimed
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserMission whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserMission whereUserId($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMission extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'mission_user';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'mission_id',
        'user_id',
        'progress',
        'current_level',
        'completed_at',
        'claimed_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'progress' => 'integer',
        'current_level' => 'integer',
        'completed_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markCompleted(): void
    {
        if ($this->completed_at === null) {
            $this->completed_at = now();
        }
    }

    public function markClaimed(): void
    {
        if ($this->claimed_at === null) {
            $this->claimed_at = now();
        }
    }
}
