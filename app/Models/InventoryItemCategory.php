<?php
/**
 * Class InventoryItemCategory
 *
 * Represents an inventory item category.
 *
 * @property int $id
 * @property string $name Name of the category
 * @property string $slug Unique slug for the category
 *
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryItemCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryItemCategory whereSlug($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItemCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }
}
