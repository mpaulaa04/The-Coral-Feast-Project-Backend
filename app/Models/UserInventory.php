<?php
/**
 * Class UserInventory
 *
 * Represents a user's inventory item in the system.
 *
 * @property int $id
 * @property int $user_id ID of the user who owns the inventory
 * @property int $inventory_item_id ID of the inventory item
 * @property int $quantity Quantity of the item
 * @property bool $is_favorite Indicates if the item is marked as favorite
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserInventory whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInventory whereInventoryItemId($value)
 */

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
