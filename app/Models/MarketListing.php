<?php
/**
 * Class MarketListing
 *
 * Represents a market listing in the system.
 *
 * @property int $id
 * @property string $type Type of the listing
 * @property int $market_listing_status_id Status ID of the listing
 * @property int $plants_id Associated plant ID
 * @property float $price Price of the listing
 * @property float $multiplier Multiplier value
 * @property CarbonInterface $starts_at Start date and time
 * @property CarbonInterface $ends_at End date and time
 * @property array $payload Additional data
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MarketListing whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketListing whereMarketListingStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketListing wherePlantsId($value)
 */

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'market_listing_status_id',
        'plants_id',
        'price',
        'multiplier',
        'starts_at',
        'ends_at',
        'payload',
    ];

    protected $casts = [
        'multiplier' => 'float',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'payload' => 'array',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(MarketListingStatus::class, 'market_listing_status_id');
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isActive(): bool
    {
        return $this->status?->slug === 'active';
    }

    public function isExpired(): bool
    {
        if (! $this->ends_at instanceof CarbonInterface) {
            return false;
        }

        return $this->ends_at->isPast();
    }
}
