<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplements', function (Blueprint $table): void {
            $table->id();
            $table->string('name')
                ->comment('Name of the supplement');
            $table->string('slug')
                ->unique()
                ->comment('Unique slug identifier for the supplement');
            $table->string('image_path')
                ->nullable()
                ->comment('Path to the image representing the supplement');
            $table->unsignedTinyInteger('health_boost')
                ->default(0)
                ->comment('Amount of health boost provided by the supplement');
            $table->boolean('hunger_reset')
                ->default(false)
                ->comment('Indicates if the supplement resets hunger');
            $table->unsignedTinyInteger('feeding_limit_bonus')
                ->default(0)
                ->comment('Bonus to the feeding limit provided by the supplement');
            $table->json('metadata')
                ->nullable()
                ->comment('Additional metadata for the supplement');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplements');
    }
};
