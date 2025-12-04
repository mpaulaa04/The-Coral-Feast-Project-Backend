¿<?php

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
        Schema::create('missions', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique()->comment('Unique mission identifier used internally.');
            $table->string('name')->nullable()->comment('Display name shown to players.');
            $table->string('description')->comment('Mission description presented in the UI.');
            $table->string('event_key')->comment('Event key that increments mission progress.');
            $table->unsignedInteger('target_amount')->default(1)->comment('Number of event hits required to complete the mission.');
            $table->unsignedInteger('reward')->default(0)->comment('Reward granted after the mission is completed.');
            $table->boolean('is_repeatable')->default(false)->comment('Determines if the mission can be repeated after completion.');
            $table->integer('sort_order')->default(0)->comment('Manual ordering used to position missions in lists.');
            $table->string('reward_image_path')->nullable()->comment('Optional image path displayed with the mission reward.');
            $table->json('metadata')->nullable()->comment('Additional mission configuration stored as JSON.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
