<?php
/**
 * Class MarketListingStatusSeeder
 *
 * Seeds market listing status types and initializes
 * the default "double_offer" market listing entry.
 */
namespace Database\Seeders;

use App\Models\MarketListing;
use App\Models\MarketListingStatus;
use Illuminate\Database\Seeder;

class MarketListingStatusSeeder extends Seeder
{
    /**
     * Run the market listing status seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Seed available listing statuses.
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
 // Get inactive status for default listing setup.
        $inactiveStatus = MarketListingStatus::where('slug', 'inactive')->first();

        if (! $inactiveStatus) {
            return;
        }
// Seed or update the default "double_offer" listing.
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
