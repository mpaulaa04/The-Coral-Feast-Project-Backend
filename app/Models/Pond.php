<?php
/**
 * Class Pond
 *
 * Represents a pond in the system.
 *
 * @property int $id
 * @property int $user_id ID of the user who owns the pond
 * @property string $name Name of the pond
 * @property string $status Status of the pond
 * @property int $current_day Current day in the pond
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Pond whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pond whereName($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pond extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'status',
        'current_day',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function slots()
    {
        return $this->hasMany(PondSlot::class);
    }
}
