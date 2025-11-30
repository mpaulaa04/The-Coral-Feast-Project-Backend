<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class PondSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->chunkById(100, function ($users): void {
            foreach ($users as $user) {
                /** @var \App\Models\User $user */
                $user->ensureDefaultPond();
            }
        });
    }
}
