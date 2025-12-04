<?php
/**
 * Class PondSlotStatus
 *
 * Represents the status of a pond slot in the system.
 *
 * @property int $id
 * @property string $name Name of the pond slot status
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PondSlotStatus whereName($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PondSlotStatus extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    public function slots()
    {
        return $this->hasMany(PondSlot::class, 'status_id');
    }
}
