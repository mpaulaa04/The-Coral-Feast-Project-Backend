<?php

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
