<?php
/**
 * Class Plant
 *
 * Represents a plant in the system.
 *
 * @property int $id
 * @property string $name Name of the plant
 * @property string $slug Unique slug for the plant
 * @property string $image_path Path to the plant image
 * @property int $oxygen_bonus Oxygen bonus provided by the plant
 * @property int $ph_bonus pH bonus provided by the plant
 * @property int $health_regeneration Health regeneration value
 * @property array $metadata Additional metadata
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Plant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plant whereSlug($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plant extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'image_path',
        'oxygen_bonus',
        'ph_bonus',
        'health_regeneration',
        'metadata',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => AsArrayObject::class,
    ];

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function pondSlots(): HasMany
    {
        return $this->hasMany(PondSlot::class);
    }
}
