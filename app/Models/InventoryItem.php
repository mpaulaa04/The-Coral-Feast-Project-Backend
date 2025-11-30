<?php

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
