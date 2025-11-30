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
            $table->string('name')->unique();
            $table->unsignedInteger('price');
            $table->string('egg_image');
            $table->string('adult_image');
            $table->string('egg_dead_image');
            $table->string('adult_dead_image');
            $table->unsignedTinyInteger('oxygen_per_day');
            $table->unsignedTinyInteger('ph_adjustment_per_day');
            $table->unsignedTinyInteger('feedings_per_day');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fish');
    }
};
