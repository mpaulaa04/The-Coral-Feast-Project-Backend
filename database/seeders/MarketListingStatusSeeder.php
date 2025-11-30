<?php

namespace Database\Seeders;

use App\Models\MarketListing;
use App\Models\MarketListingStatus;
use Illuminate\Database\Seeder;

class MarketListingStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['slug' => 'inactive', 'label' => 'Inactive'],
            ['slug' => 'active', 'label' => 'Active'],
            ['slug' => 'scheduled', 'label' => 'Scheduled'],
        ];

        foreach ($statuses as $status) {
            MarketListingStatus::updateOrCreate(
                ['slug' => $status['slug']],
                ['label' => $status['label']]
            );
        }

        $inactiveStatus = MarketListingStatus::where('slug', 'inactive')->first();

        if (! $inactiveStatus) {
            return;
        }

        MarketListing::updateOrCreate(
            ['type' => 'double_offer'],
            [
                'market_listing_status_id' => $inactiveStatus->id,
                'multiplier' => 1,
                'starts_at' => null,
                'ends_at' => null,
                'payload' => [],
            ]
        );
    }
}
