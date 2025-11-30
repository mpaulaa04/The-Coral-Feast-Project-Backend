<?php

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
