<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('days_played')
                ->default(0)
                ->comment('Tracks the total number of days the user has played');
            $table->timestampTz('last_played_at')
                ->nullable()
                ->comment('Stores the last time the user played');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['days_played', 'last_played_at']);
        });
    }
};
