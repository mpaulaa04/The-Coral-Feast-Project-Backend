<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Pond;
use App\Models\PondSlot;

trait SerializesPonds
{
    protected function serializeSlot(PondSlot $slot): array
    {
        $slot->loadMissing(['fish', 'status', 'plant']);
        $slot->refreshStageProgress();

        $fish = $slot->fish;
        $status = $slot->status;
        $plant = $slot->plant;

        return [
            'id' => $slot->id,
            'pond_id' => $slot->pond_id,
            'status' => [
                'id' => $slot->status_id,
                'name' => $status?->name,
            ],
            'plant_id' => $slot->plant_id,
            'fish' => $fish ? [
                'id' => $fish->id,
                'name' => $fish->name,
                'price' => $fish->price,
                'egg_image' => $fish->egg_image,
                'adult_image' => $fish->adult_image,
                'egg_dead_image' => $fish->egg_dead_image,
                'adult_dead_image' => $fish->adult_dead_image,
                'oxygen_per_day' => $fish->oxygen_per_day,
                'ph_adjustment_per_day' => $fish->ph_adjustment_per_day,
                'feedings_per_day' => $fish->feedings_per_day,
                'egg_stage_seconds' => $fish->egg_stage_seconds,
                'juvenile_stage_seconds' => $fish->juvenile_stage_seconds,
                'adult_stage_seconds' => $fish->adult_stage_seconds,
            ] : null,
            'health' => $slot->health,
            'oxygen_level' => $slot->oxygen_level,
            'ph_level' => $slot->ph_level,
            'feeding_count' => $slot->feeding_count,
            'feeding_limit' => $slot->feeding_limit,
            'has_ph_issue' => $slot->has_ph_issue,
            'has_oxygen_issue' => $slot->has_oxygen_issue,
            'has_temperature_issue' => $slot->has_temperature_issue,
            'has_water_quality_issue' => $slot->has_water_quality_issue,
            'plant' => $plant ? [
                'id' => $plant->id,
                'name' => $plant->name,
                'slug' => $plant->slug,
                'image_path' => $plant->image_path,
                'oxygen_bonus' => $plant->oxygen_bonus,
                'ph_bonus' => $plant->ph_bonus,
                'health_regeneration' => $plant->health_regeneration,
                'metadata' => (array) $plant->metadata,
                'placed_at' => optional($slot->plant_placed_at)->toIso8601String(),
            ] : null,
            'stage_started_at' => optional($slot->stage_started_at)->toIso8601String(),
            'stage_progress_seconds' => $slot->stage_progress_seconds,
            'stage_duration_seconds' => $slot->stage_duration_seconds,
            'last_fed_at' => optional($slot->last_fed_at)->toIso8601String(),
            'last_oxygenated_at' => optional($slot->last_oxygenated_at)->toIso8601String(),
            'last_ph_adjusted_at' => optional($slot->last_ph_adjusted_at)->toIso8601String(),
            'last_cleaned_at' => optional($slot->last_cleaned_at)->toIso8601String(),
            'created_at' => optional($slot->created_at)->toIso8601String(),
            'updated_at' => optional($slot->updated_at)->toIso8601String(),
        ];
    }

    protected function serializePond(Pond $pond): array
    {
        $pond->loadMissing(['slots.fish', 'slots.status', 'slots.plant']);

        $slots = $pond->slots
            ->map(fn (PondSlot $slot) => $this->serializeSlot($slot))
            ->values();

        $statusCounts = $pond->slots
            ->groupBy(fn (PondSlot $slot) => $slot->status?->name ?? 'unknown')
            ->map(fn ($group) => $group->count())
            ->toArray();

        return [
            'id' => $pond->id,
            'user_id' => $pond->user_id,
            'name' => $pond->name,
            'status' => $pond->status,
            'slot_summary' => [
                'total' => $slots->count(),
                'available' => $slots->where('status.name', 'empty')->count(),
                'occupied' => $slots->filter(fn ($slot) => in_array($slot['status']['name'], ['egg', 'juvenile', 'adult'], true))->count(),
                'dead' => $slots->where('status.name', 'dead')->count(),
                'by_status' => $statusCounts,
            ],
            'slots' => $slots,
            'created_at' => optional($pond->created_at)->toIso8601String(),
            'updated_at' => optional($pond->updated_at)->toIso8601String(),
        ];
    }
}
