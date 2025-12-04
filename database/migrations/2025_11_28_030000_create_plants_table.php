<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plants', function (Blueprint $table): void {
            $table->id();
            $table->string('name')
                ->comment('Name of the plant');
            $table->string('slug')
                ->unique()
                ->comment('Unique slug identifier for the plant');
            $table->string('image_path')
                ->nullable()
                ->comment('Path to the image representing the plant');
            $table->unsignedTinyInteger('oxygen_bonus')
                ->default(0)
                ->comment('Oxygen bonus provided by the plant');
            $table->unsignedTinyInteger('ph_bonus')
                ->default(0)
                ->comment('pH bonus provided by the plant');
            $table->unsignedTinyInteger('health_regeneration')
                ->default(0)
                ->comment('Health regeneration value provided by the plant');
            $table->json('metadata')
                ->nullable()
                ->comment('Additional metadata for the plant');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plants');
    }
};
