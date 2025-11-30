<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInventory extends Model
{
    use HasFactory;

    protected $table = 'user_inventory';

    protected $fillable = [
        'user_id',
        'inventory_item_id',
        'quantity',
        'is_favorite',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_favorite' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
