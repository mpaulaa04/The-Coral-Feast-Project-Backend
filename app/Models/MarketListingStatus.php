<?php
/**
 * Class MarketListingStatus
 *
 * Represents the status of a market listing.
 *
 * @property int $id
 * @property string $slug Unique slug for the status
 * @property string $label Human-readable label for the status
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MarketListingStatus whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketListingStatus whereLabel($value)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketListingStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'label',
    ];

    public function listings(): HasMany
    {
        return $this->hasMany(MarketListing::class, 'market_listing_status_id');
    }
}
