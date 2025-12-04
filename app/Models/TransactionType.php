<?php
/**
 * Class TransactionType
 *
 * Represents a type of wallet transaction in the system.
 *
 * @property int $id
 * @property string $transaction Name of the transaction type
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TransactionType whereTransaction($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
