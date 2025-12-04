<?php
/**
 * Class DatabaseSeeder
 *
 * Seeds the application's core data including users, ponds,
 * missions, inventory items, statuses, and wallet information.
 */
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\MarketListingStatusSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
  // User::factory(10)->create();
 // Create or update the default testing user.
 // This allows immediate access to game entities during development.
       
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'farm_name' => 'Coral Starter',
                'farm_type' => 'saltwater',
                'email_verified_at' => now(),
                'password' => 'password',
            ]
        );
// Register all application-specific seeders.
 // The order matters for relations that depend on other tables.
       
        $this->call([
            TransactionTypeSeeder::class,
            PondSlotStatusSeeder::class,
            MarketListingStatusSeeder::class,
            NotificationTypeSeeder::class,
            FishSeeder::class,
            InventorySeeder::class,
            MissionSeeder::class,
            WalletSeeder::class,
            PondSeeder::class,
        ]);

    }
}
