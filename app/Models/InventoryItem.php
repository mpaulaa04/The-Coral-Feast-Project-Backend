<?php
/**
 * Class InventoryItem
 *
 * Represents an item in the global inventory system.
 *
 * @property int $id
 * @property int $inventory_item_category_id Category ID of the item
 * @property string $name Name of the item
 * @property int $price Price of the item
 * @property string $slug Unique slug identifier
 * @property string|null $image_path Main display image
 * @property string|null $pond_egg_image_path Pond image (egg state)
 * @property string|null $pond_adult_image_path Pond image (adult state)
 * @property string|null $pond_egg_dead_image_path Pond image (egg dead)
 * @property string|null $pond_adult_dead_image_path Pond image (adult dead)
 * @property int|null $fish_id Linked fish ID (if the item is a fish)
 * @property int|null $plant_id Linked plant ID (if the item is a plant)
 * @property int|null $supplement_id Linked supplement ID (if the item is a supplement)
 * @property array|null $metadata Additional metadata (JSON)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryItem whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryItem whereInventoryItemCategoryId($value)
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_category_id',
        'name',
        'price',
        'slug',
        'image_path',
        'pond_egg_image_path',
        'pond_adult_image_path',
        'pond_egg_dead_image_path',
        'pond_adult_dead_image_path',
        'fish_id',
        'plant_id',
        'supplement_id',
        'metadata',
    ];

    protected $casts = [
        'price' => 'integer',
        'metadata' => AsArrayObject::class,
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryItemCategory::class, 'inventory_item_category_id');
    }

    public function fish(): BelongsTo
    {
        return $this->belongsTo(Fish::class);
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function supplement(): BelongsTo
    {
        return $this->belongsTo(Supplement::class);
    }

    public function userInventory(): HasMany
    {
        return $this->hasMany(UserInventory::class);
    }
}
