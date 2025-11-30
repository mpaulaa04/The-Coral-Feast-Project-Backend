<?php

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
