<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fish', function (Blueprint $table) {
            $table->id();
            $table->string('name')
                ->unique()
                ->comment('Unique name of the fish species');
            $table->unsignedInteger('price')
                ->comment('Price of the fish');
            $table->string('egg_image')
                ->comment('Path to the egg stage image');
            $table->string('adult_image')
                ->comment('Path to the adult stage image');
            $table->string('egg_dead_image')
                ->comment('Path to the dead egg image');
            $table->string('adult_dead_image')
                ->comment('Path to the dead adult image');
            $table->unsignedTinyInteger('oxygen_per_day')
                ->comment('Oxygen required per day');
            $table->unsignedTinyInteger('ph_adjustment_per_day')
                ->comment('pH adjustments required per day');
            $table->unsignedTinyInteger('feedings_per_day')
                ->comment('Number of feedings required per day');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fish');
    }
};
