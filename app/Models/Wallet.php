<?php
/**
 * Class Wallet
 *
 * Represents a user's wallet in the system.
 *
 * @property int $id
 * @property int $user_id ID of the user who owns the wallet
 * @property int $balance Current balance of the wallet
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereUserId($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
    ];

    protected $casts = [
        'balance' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
