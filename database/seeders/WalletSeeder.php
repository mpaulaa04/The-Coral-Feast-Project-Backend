<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $initialBalance = User::INITIAL_WALLET_BALANCE;
        $initialType = TransactionType::firstOrCreate([
            'transaction' => 'initial_bonus',
        ]);

        User::query()->chunkById(100, function ($users) use ($initialBalance, $initialType): void {
            foreach ($users as $user) {
                /** @var \App\Models\User $user */
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => $initialBalance]
                );

                if ($wallet->balance < $initialBalance) {
                    $wallet->balance = $initialBalance;
                    $wallet->save();
                }

                $hasInitial = $wallet->transactions()
                    ->where('transaction_type_id', $initialType->id)
                    ->where('event', 'Initial wallet funding')
                    ->exists();

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
