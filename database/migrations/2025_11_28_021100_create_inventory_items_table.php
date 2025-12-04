<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_item_category_id')
                ->constrained('inventory_item_categories')
                ->cascadeOnDelete()
                ->comment('References the category of the inventory item');
            $table->string('name')
                ->comment('Name of the inventory item');
            $table->unsignedInteger('price')
                ->default(0)
                ->comment('Price of the inventory item');
            $table->string('slug')
                ->unique()
                ->comment('Unique slug identifier for the inventory item');
            $table->string('image_path')
                ->comment('Path to the main image of the inventory item');
            $table->string('pond_egg_image_path')
                ->nullable()
                ->comment('Path to the egg image for pond display');
            $table->string('pond_adult_image_path')
                ->nullable()
                ->comment('Path to the adult image for pond display');
            $table->string('pond_egg_dead_image_path')
                ->nullable()
                ->comment('Path to the dead egg image for pond display');
            $table->string('pond_adult_dead_image_path')
                ->nullable()
                ->comment('Path to the dead adult image for pond display');
            $table->foreignId('fish_id')
                ->nullable()
                ->constrained('fish')
                ->nullOnDelete()
                ->comment('References the fish associated with the inventory item');
            $table->json('metadata')
                ->nullable()
                ->comment('Additional metadata for the inventory item');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
