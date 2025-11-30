<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PondSlotStatus;

class PondSlotStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            'empty',
            'egg',
            'juvenile',
            'adult',
            'dead',
        ];

        foreach ($statuses as $name) {
            PondSlotStatus::firstOrCreate([
                'name' => $name,
            ]);
        }
    }
}
