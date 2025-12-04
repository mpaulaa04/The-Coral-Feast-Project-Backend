<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_listings', function (Blueprint $table) {
            $table->id();
            $table->string('type')
                ->comment('Type of the market listing');
            $table->foreignId('market_listing_status_id')
                ->constrained('market_listing_statuses')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('References the status of the market listing');
            $table->foreignId('plants_id')
                ->nullable()
                ->constrained('plants')
                ->nullOnDelete()
                ->cascadeOnUpdate()
                ->comment('References the plant associated with the listing');
            $table->unsignedInteger('price')
                ->nullable()
                ->comment('Price of the market listing');
            $table->decimal('multiplier', 8, 2)
                ->default(1)
                ->comment('Multiplier value for the listing');
            $table->timestamp('starts_at')
                ->nullable()
                ->comment('Start time of the market listing');
            $table->timestamp('ends_at')
                ->nullable()
                ->comment('End time of the market listing');
            $table->json('payload')
                ->nullable()
                ->comment('Additional data for the market listing');
            $table->timestamps();

            $table->unique('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_listings');
    }
};
