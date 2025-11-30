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
            $table->foreignId('inventory_item_category_id')->constrained('inventory_item_categories')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('price')->default(0);
            $table->string('slug')->unique();
            $table->string('image_path');
            $table->string('pond_egg_image_path')->nullable();
            $table->string('pond_adult_image_path')->nullable();
            $table->string('pond_egg_dead_image_path')->nullable();
            $table->string('pond_adult_dead_image_path')->nullable();
            $table->foreignId('fish_id')->nullable()->constrained('fish')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
