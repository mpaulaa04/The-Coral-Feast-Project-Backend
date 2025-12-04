<?php
/**
 * Class WalletController
 *
 * Handles API requests related to user wallet management and transactions.
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TransactionType;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
      /**
     * Display a user's wallet details, optionally including recent transactions.
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function show(User $user, Request $request): JsonResponse
    {
        $wallet = $user->wallet()->firstOrCreate([], [
            'balance' => User::INITIAL_WALLET_BALANCE,
        ]);

        $data = $this->serializeWallet($wallet);

        if ($request->boolean('include_transactions')) {
            $transactions = $wallet->transactions()
                ->latest('created_at')
                ->limit((int) $request->query('transactions_limit', 20))
                ->get()
                ->map(fn (WalletTransaction $transaction) => [
                    'id' => $transaction->id,
                    'transaction_type_id' => $transaction->transaction_type_id,
                    'type' => optional($transaction->type)->transaction,
                    'amount' => $transaction->amount,
                    'event' => $transaction->event,
                    'created_at' => optional($transaction->created_at)->toIso8601String(),
                    'updated_at' => optional($transaction->updated_at)->toIso8601String(),
                ])
                ->all();

            $data['transactions'] = $transactions;
        }

        return response()->json(['data' => $data]);
    }
/**
     * Update a user's wallet balance and register the corresponding transaction.
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function update(User $user, Request $request): JsonResponse
    {
        $payload = $request->validate([
            'user_id' => ['required', 'integer'],
            'balance' => ['required', 'integer', 'min:0'],
            'transaction_type' => ['nullable', 'string', 'max:100'],
            'event' => ['nullable', 'string', 'max:255'],
        ]);

        if ((int) $payload['user_id'] !== $user->id) {
            abort(403, 'The provided user does not own this wallet.');
        }

        /** @var Wallet $wallet */
        $wallet = $user->wallet()->firstOrCreate([], [
            'balance' => User::INITIAL_WALLET_BALANCE,
        ]);

        $targetBalance = (int) $payload['balance'];
        $previousBalance = (int) $wallet->balance;
        $delta = $targetBalance - $previousBalance;

        DB::transaction(function () use ($wallet, $payload, $targetBalance, $delta): void {
            $wallet->forceFill([
                'balance' => $targetBalance,
            ])->save();

            if ($delta === 0) {
                return;
            }

            $typeSlug = $payload['transaction_type'] ?? null;
            $typeSlug = $typeSlug !== null ? trim((string) $typeSlug) : null;

            if ($typeSlug === null || $typeSlug === '') {
                $typeSlug = $delta > 0 ? 'sale' : 'purchase';
            }

            $transactionType = TransactionType::firstOrCreate([
                'transaction' => $typeSlug,
            ]);

            $eventLabel = isset($payload['event']) ? trim((string) $payload['event']) : null;

            $wallet->transactions()->create([
                'transaction_type_id' => $transactionType->id,
                'amount' => $delta,
                'event' => $eventLabel ?: null,
            ]);
        });

        return response()->json([
            'data' => $this->serializeWallet($wallet->fresh()),
        ]);
    }

     /**
     * Serialize wallet model into a consistent API response format.
     *
     * @param Wallet $wallet
     * @return array<string, mixed>
     */
    private function serializeWallet(Wallet $wallet): array
    {
        return [
            'id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'balance' => $wallet->balance,
            'created_at' => optional($wallet->created_at)->toIso8601String(),
            'updated_at' => optional($wallet->updated_at)->toIso8601String(),
        ];
    }
}
