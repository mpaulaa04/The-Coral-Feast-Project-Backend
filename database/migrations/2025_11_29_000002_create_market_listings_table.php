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
            $table->string('type');
            $table->foreignId('market_listing_status_id')
                ->constrained('market_listing_statuses')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('plants_id')
                ->nullable()
                ->constrained('plants')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->unsignedInteger('price')->nullable();
            $table->decimal('multiplier', 8, 2)->default(1);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_listings');
    }
};
