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
        Schema::create('mission_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete()->comment('Mission being tracked for the user.');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->comment('User owning this mission progress record.');
            $table->unsignedInteger('progress')->default(0)->comment('Progress counter toward mission completion.');
            $table->timestamp('completed_at')->nullable()->comment('Date the user completed the mission goals.');
            $table->timestamp('claimed_at')->nullable()->comment('Date the user claimed the mission reward.');
            $table->timestamps();

            $table->unique(['mission_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_user');
    }
};
