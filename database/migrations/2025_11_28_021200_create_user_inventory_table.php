<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_inventory', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->comment('References the user who owns the inventory record');
            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')
                ->cascadeOnDelete()
                ->comment('References the inventory item');
            $table->unsignedInteger('quantity')
                ->default(0)
                ->comment('Quantity of the inventory item owned by the user');
            $table->boolean('is_favorite')
                ->default(false)
                ->comment('Indicates if the item is marked as favorite by the user');
            $table->timestamps();

            $table->unique(['user_id', 'inventory_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_inventory');
    }
};
