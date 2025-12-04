<?php
/**
 * Class PondSeeder
 *
 * Ensures every user in the system has a default pond created.
 */
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class PondSeeder extends Seeder
{
    /**
     * Run the pond seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Process users in chunks to avoid memory overhead.
        User::query()->chunkById(100, function ($users): void {
            foreach ($users as $user) {
                /** @var \App\Models\User $user */
                $user->ensureDefaultPond();
            }
        });
    }
}
