<?php
/**
 * Class PondSlotStatusSeeder
 *
 * Seeds the available pond slot status values used to classify
 * fish development stages inside the pond.
 */
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PondSlotStatus;

class PondSlotStatusSeeder extends Seeder
{
     /**
     * Run the pond slot status seeds.
     *
     * @return void
     */
    public function run(): void
    {
         // List of basic pond slot statuses.
        $statuses = [
            'empty',
            'egg',
            'juvenile',
            'adult',
            'dead',
        ];
// Seed or create each status entry.
        foreach ($statuses as $name) {
            PondSlotStatus::firstOrCreate([
                'name' => $name,
            ]);
        }
    }
}
