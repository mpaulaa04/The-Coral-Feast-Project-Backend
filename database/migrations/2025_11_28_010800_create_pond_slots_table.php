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
            $table->foreignId('pond_id')
                ->constrained()
                ->cascadeOnDelete()
                ->comment('References the pond this slot belongs to');
            $table->foreignId('fish_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->comment('References the fish occupying this slot');
            $table->foreignId('status_id')
                ->constrained('pond_slot_statuses')
                ->comment('References the current status of the pond slot');
            $table->unsignedTinyInteger('health')
                ->default(100)
                ->comment('Health level of the fish in the slot');
            $table->unsignedTinyInteger('oxygen_level')
                ->default(100)
                ->comment('Oxygen level in the pond slot');
            $table->unsignedTinyInteger('ph_level')
                ->default(100)
                ->comment('pH level in the pond slot');
            $table->unsignedTinyInteger('feeding_count')
                ->default(0)
                ->comment('Number of times the fish has been fed');
            $table->unsignedTinyInteger('feeding_limit')
                ->default(3)
                ->comment('Maximum number of feedings allowed');
            $table->boolean('has_ph_issue')
                ->default(false)
                ->comment('Indicates if there is a pH issue');
            $table->boolean('has_oxygen_issue')
                ->default(false)
                ->comment('Indicates if there is an oxygen issue');
            $table->boolean('has_temperature_issue')
                ->default(false)
                ->comment('Indicates if there is a temperature issue');
            $table->timestamp('stage_started_at')
                ->nullable()
                ->comment('Timestamp when the current stage started');
            $table->timestamp('last_fed_at')
                ->nullable()
                ->comment('Timestamp of the last feeding');
            $table->timestamp('last_oxygenated_at')
                ->nullable()
                ->comment('Timestamp of the last oxygenation');
            $table->timestamp('last_ph_adjusted_at')
                ->nullable()
                ->comment('Timestamp of the last pH adjustment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pond_slots');
    }
};
