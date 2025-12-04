<?php
/**
 * Class WalletTransaction
 *
 * Represents a transaction in a user's wallet.
 *
 * @property int $id
 * @property int $wallet_id ID of the associated wallet
 * @property int $transaction_type_id ID of the transaction type
 * @property int $amount Amount of the transaction
 * @property string $event Event or description of the transaction
 *
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereWalletId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereTransactionTypeId($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'transaction_type_id',
        'amount',
        'event',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type_id');
    }
}
