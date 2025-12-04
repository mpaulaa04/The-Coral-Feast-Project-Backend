<?php
/**
 * Class PondSlot
 *
 * Represents a slot in a pond, which can hold a fish and/or a plant, and tracks its state.
 *
 * @property int $id
 * @property int $pond_id ID of the associated pond
 * @property int|null $fish_id ID of the fish in the slot
 * @property int|null $plant_id ID of the plant in the slot
 * @property int $status_id Status ID of the slot
 * @property int $health Health value of the slot
 * @property int $oxygen_level Oxygen level in the slot
 * @property int $ph_level pH level in the slot
 * @property int $feeding_count Number of times fed
 * @property int $feeding_limit Feeding limit
 * @property bool $has_ph_issue Indicates if there is a pH issue
 * @property bool $has_oxygen_issue Indicates if there is an oxygen issue
 * @property bool $has_temperature_issue Indicates if there is a temperature issue
 * @property bool $has_water_quality_issue Indicates if there is a water quality issue
 * @property string|null $stage_started_at Timestamp when the stage started
 * @property string|null $plant_placed_at Timestamp when the plant was placed
 * @property string|null $plant_effect_state State of the plant effect
 * @property string|null $plant_effect_expires_at Timestamp when the plant effect expires
 * @property int $stage_progress_seconds Progress in seconds for the current stage
 * @property int $stage_duration_seconds Duration in seconds for the current stage
 * @property string|null $last_fed_at Timestamp when last fed
 * @property string|null $last_oxygenated_at Timestamp when last oxygenated
 * @property string|null $last_ph_adjusted_at Timestamp when last pH adjusted
 * @property string|null $last_cleaned_at Timestamp when last cleaned
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PondSlot wherePondId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PondSlot whereFishId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PondSlot wherePlantId($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PondSlot extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'pond_id',
        'fish_id',
        'plant_id',
        'status_id',
        'health',
        'oxygen_level',
        'ph_level',
        'feeding_count',
        'feeding_limit',
        'has_ph_issue',
        'has_oxygen_issue',
        'has_temperature_issue',
        'has_water_quality_issue',
        'stage_started_at',
        'plant_placed_at',
        'plant_effect_state',
        'plant_effect_expires_at',
        'stage_progress_seconds',
        'stage_duration_seconds',
        'last_fed_at',
        'last_oxygenated_at',
        'last_ph_adjusted_at',
        'last_cleaned_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'health' => 'integer',
        'oxygen_level' => 'integer',
        'ph_level' => 'integer',
        'feeding_count' => 'integer',
        'feeding_limit' => 'integer',
        'has_ph_issue' => 'boolean',
        'has_oxygen_issue' => 'boolean',
        'has_temperature_issue' => 'boolean',
        'has_water_quality_issue' => 'boolean',
        'stage_started_at' => 'datetime',
        'plant_placed_at' => 'datetime',
        'plant_effect_state' => 'array',
        'plant_effect_expires_at' => 'datetime',
        'stage_progress_seconds' => 'integer',
        'stage_duration_seconds' => 'integer',
        'last_fed_at' => 'datetime',
        'last_oxygenated_at' => 'datetime',
        'last_ph_adjusted_at' => 'datetime',
        'last_cleaned_at' => 'datetime',
    ];

    public function pond()
    {
        return $this->belongsTo(Pond::class);
    }

    public function fish()
    {
        return $this->belongsTo(Fish::class);
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function status()
    {
        return $this->belongsTo(PondSlotStatus::class, 'status_id');
    }

    public function refreshStageProgress(): void
    {
        if (! $this->stage_started_at) {
            if ($this->stage_progress_seconds !== 0) {
                $this->forceFill([
                    'stage_progress_seconds' => 0,
                ])->saveQuietly();
            }

            return;
        }

        $elapsed = $this->stage_started_at->diffInSeconds(now());
        $duration = (int) $this->stage_duration_seconds;

        if ($duration > 0) {
            $elapsed = min($elapsed, $duration);
        }

        if ((int) $this->stage_progress_seconds !== $elapsed) {
            $this->forceFill([
                'stage_progress_seconds' => $elapsed,
            ])->saveQuietly();
        }
    }
}
