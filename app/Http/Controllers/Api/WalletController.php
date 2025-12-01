<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show(User $user, Request $request): JsonResponse
    {
        $wallet = $user->wallet()->firstOrCreate([], [
            'balance' => User::INITIAL_WALLET_BALANCE,
        ]);

        $data = [
            'id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'balance' => $wallet->balance,
            'created_at' => optional($wallet->created_at)->toIso8601String(),
            'updated_at' => optional($wallet->updated_at)->toIso8601String(),
        ];

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
}
