<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $types = [
            'initial_bonus',
            'purchase',
            'reward',
            'refund',
        ];

        foreach ($types as $transaction) {
            TransactionType::firstOrCreate([
                'transaction' => $transaction,
            ]);
        }
    }
}
