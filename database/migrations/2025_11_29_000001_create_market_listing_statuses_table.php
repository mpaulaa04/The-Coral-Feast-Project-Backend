<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_listing_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')
                ->unique()
                ->comment('Unique slug identifier for the market listing status');
            $table->string('label')
                ->comment('Human-readable label for the market listing status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_listing_statuses');
    }
};
