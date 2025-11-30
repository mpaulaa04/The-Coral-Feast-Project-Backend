<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pond_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pond_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fish_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('status_id')->constrained('pond_slot_statuses');
            $table->unsignedTinyInteger('health')->default(100);
            $table->unsignedTinyInteger('oxygen_level')->default(100);
            $table->unsignedTinyInteger('ph_level')->default(100);
            $table->unsignedTinyInteger('feeding_count')->default(0);
            $table->unsignedTinyInteger('feeding_limit')->default(3);
            $table->boolean('has_ph_issue')->default(false);
            $table->boolean('has_oxygen_issue')->default(false);
            $table->boolean('has_temperature_issue')->default(false);
            $table->timestamp('stage_started_at')->nullable();
            $table->timestamp('last_fed_at')->nullable();
            $table->timestamp('last_oxygenated_at')->nullable();
            $table->timestamp('last_ph_adjusted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pond_slots');
    }
};
