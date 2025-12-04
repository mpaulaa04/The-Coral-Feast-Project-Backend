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
            $table->boolean('tutorial_completed')
                ->default(false)
                ->comment('Indicates if the user has completed the tutorial');
            $table->string('tutorial_step')
                ->nullable()
                ->comment('Stores the current tutorial step for the user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tutorial_completed', 'tutorial_step']);
        });
    }
};
