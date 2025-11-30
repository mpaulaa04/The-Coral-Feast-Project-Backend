<?php

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
