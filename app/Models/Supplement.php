<?php
/**
 * Class Supplement
 *
 * Represents a supplement item used to boost fish stats or interactions.
 *
 * @property int $id
 * @property string $name Name of the supplement
 * @property string $slug Unique slug identifier
 * @property string|null $image_path Supplement display image
 * @property int|null $health_boost Additional health provided to the fish
 * @property bool $hunger_reset Whether this supplement resets hunger
 * @property int|null $feeding_limit_bonus Bonus feeding limit applied
 * @property array|null $metadata Additional metadata (JSON)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Supplement whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplement whereSlug($value)
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplement extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'image_path',
        'health_boost',
        'hunger_reset',
        'feeding_limit_bonus',
        'metadata',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'hunger_reset' => 'boolean',
        'metadata' => AsArrayObject::class,
    ];

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }
}
