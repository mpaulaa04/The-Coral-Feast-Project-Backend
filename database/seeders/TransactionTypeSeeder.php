<?php
/**
 * Class TransactionTypeSeeder
 *
 * Seeds transaction types used for wallet logs such as purchases,
 * rewards, refunds, and initial bonus operations.
 */
namespace Database\Seeders;

use App\Models\TransactionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    use WithoutModelEvents;
/**
     * Run the transaction type seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Basic list of wallet transaction types.
        $types = [
            'initial_bonus',
            'purchase',
            'reward',
            'refund',
        ];
// Seed or create each transaction type.
        foreach ($types as $transaction) {
            TransactionType::firstOrCreate([
                'transaction' => $transaction,
            ]);
        }
    }
}
