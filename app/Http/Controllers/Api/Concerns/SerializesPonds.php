<?php
/**
 * Trait SerializesPonds
 *
 * Provides helper methods to serialize pond and pond slot data
 * into consistent array structures for API responses.
 */
namespace App\Http\Controllers\Api\Concerns;

use App\Models\Pond;
use App\Models\PondSlot;

trait SerializesPonds
{
     /**
     * Serialize a pond slot and its related fish, plant, and status data.
     *
     * @param PondSlot $slot
     * @return array<string, mixed>
     */
    protected function serializeSlot(PondSlot $slot): array
    {
        $this->expirePlantIfNeeded($slot);

        $slot->loadMissing(['fish', 'status', 'plant']);
        $slot->refreshStageProgress();

        $fish = $slot->fish;
        $status = $slot->status;
        $plant = $slot->plant;
        $effectState = $slot->plant_effect_state ?? [];

        if ($effectState instanceof \ArrayAccess) {
            $effectState = (array) $effectState;
        }

        if (! is_array($effectState)) {
            $effectState = [];
        }

        $plantEffect = null;

        if (! empty($effectState) || $slot->plant_effect_expires_at) {
            $plantEffect = [
                'state' => ! empty($effectState) ? $effectState : null,
                'expires_at' => optional($slot->plant_effect_expires_at)->toIso8601String(),
            ];
        }

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
            'plant_effect' => $plantEffect,
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

    /**
     * Ensure that plant effects are correctly expired or updated
     * based on configured lifetime rules for the slot.
     *
     * @param PondSlot $slot
     * @return void
     */
    protected function expirePlantIfNeeded(PondSlot $slot): void
    {
        $maxLifetime = max(0, (int) $this->plantEffectMaxLifetimeSeconds());
        $expiresAt = $slot->plant_effect_expires_at;
        $placedAt = $slot->plant_placed_at;

        $deadline = null;

        if ($expiresAt) {
            $deadline = clone $expiresAt;
        }

        if ($maxLifetime > 0 && $placedAt) {
            $maxDeadline = (clone $placedAt)->addSeconds($maxLifetime);

            if (! $deadline || $deadline->gt($maxDeadline)) {
                $deadline = $maxDeadline;
            }
        }

        if (! $deadline) {
            return;
        }

        $updates = [];

        if (! $expiresAt || ! $expiresAt->equalTo($deadline)) {
            $updates['plant_effect_expires_at'] = $deadline;
        }

        $state = $slot->plant_effect_state;
        if ($maxLifetime > 0) {
            if (is_array($state)) {
                $currentLifetime = (int) ($state['lifetime_seconds'] ?? 0);

                if ($currentLifetime <= 0 || $currentLifetime > $maxLifetime) {
                    $state['lifetime_seconds'] = $maxLifetime;
                    $updates['plant_effect_state'] = $state;
                }
            } elseif ($slot->plant_id) {
                $updates['plant_effect_state'] = ['lifetime_seconds' => $maxLifetime];
            }
        }

        if ($updates) {
            $slot->forceFill($updates)->save();
        }

        if (now()->lt($deadline)) {
            return;
        }

        $slot->forceFill([
            'plant_id' => null,
            'plant_placed_at' => null,
            'plant_effect_state' => null,
            'plant_effect_expires_at' => null,
        ])->save();

        $slot->unsetRelation('plant');
    }
/**
     * Get the max lifetime allowed for plant effects in seconds.
     *
     * @return int
     */
    protected function plantEffectMaxLifetimeSeconds(): int
    {
        $constantQualifiedName = static::class . '::MAX_PLANT_EFFECT_DURATION_SECONDS';

        if (defined($constantQualifiedName)) {
            return (int) constant($constantQualifiedName);
        }

        return 30;
    }
 /**
     * Serialize an entire pond along with its slot summary data.
     *
     * @param Pond $pond
     * @return array<string, mixed>
     */
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
            'current_day' => (int) ($pond->current_day ?? 1),
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
