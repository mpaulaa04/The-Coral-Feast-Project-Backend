<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')
                ->constrained()
                ->cascadeOnDelete()
                ->comment('References the wallet associated with the transaction');
            $table->foreignId('transaction_type_id')
                ->constrained()
                ->cascadeOnDelete()
                ->comment('References the type of transaction');
            $table->bigInteger('amount')
                ->comment('Transaction amount, positive or negative');
            $table->string('event')
                ->nullable()
                ->comment('Event or context for the transaction');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
