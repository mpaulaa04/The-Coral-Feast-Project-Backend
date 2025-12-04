<?php
/**
 * Class Fish
 *
 * Represents a fish in the system.
 *
 * @property int $id
 * @property string $name Name of the fish
 * @property string $species Species of the fish
 * @property int $pond_id Associated pond ID
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Fish whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fish whereSpecies($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fish extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'price',
        'egg_image',
        'adult_image',
        'egg_dead_image',
        'adult_dead_image',
        'oxygen_per_day',
        'ph_adjustment_per_day',
        'feedings_per_day',
        'egg_stage_seconds',
        'juvenile_stage_seconds',
        'adult_stage_seconds',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'integer',
        'oxygen_per_day' => 'integer',
        'ph_adjustment_per_day' => 'integer',
        'feedings_per_day' => 'integer',
        'egg_stage_seconds' => 'integer',
        'juvenile_stage_seconds' => 'integer',
        'adult_stage_seconds' => 'integer',
    ];
}
