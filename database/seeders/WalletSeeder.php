<?php
/**
 * Class WalletSeeder
 *
 * Ensures all users have a wallet initialized with the default
 * starting balance and registers an initial transaction record
 * if it has not been logged before.
 */
namespace Database\Seeders;

use App\Models\TransactionType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    use WithoutModelEvents;
/**
     * Run the wallet seeds.
     *
     * @return void
     */
    public function run(): void
    {
         // Ensure the "initial_bonus" transaction type exists.
        $initialBalance = User::INITIAL_WALLET_BALANCE;
        $initialType = TransactionType::firstOrCreate([
            'transaction' => 'initial_bonus',
        ]);
 // Process users in manageable chunks.
        User::query()->chunkById(100, function ($users) use ($initialBalance, $initialType): void {
            foreach ($users as $user) {
                /** @var \App\Models\User $user */
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => $initialBalance]
                );
// Ensure minimum initial balance.
                if ($wallet->balance < $initialBalance) {
                    $wallet->balance = $initialBalance;
                    $wallet->save();
                }
// Check if the initial funding transaction exists.
                $hasInitial = $wallet->transactions()
                    ->where('transaction_type_id', $initialType->id)
                    ->where('event', 'Initial wallet funding')
                    ->exists();
// Create the initial balance transaction if missing.
                if (!$hasInitial && $initialBalance !== 0) {
                    $wallet->transactions()->create([
                        'transaction_type_id' => $initialType->id,
                        'amount' => $initialBalance,
                        'event' => 'Initial wallet funding',
                    ]);
                }
            }
        });
    }
}
