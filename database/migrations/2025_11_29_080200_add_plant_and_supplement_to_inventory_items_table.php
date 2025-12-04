<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->foreignId('plant_id')
                ->nullable()
                ->after('fish_id')
                ->constrained('plants')
                ->nullOnDelete()
                ->comment('References the plant associated with the inventory item');
            $table->foreignId('supplement_id')
                ->nullable()
                ->after('plant_id')
                ->constrained('supplements')
                ->nullOnDelete()
                ->comment('References the supplement associated with the inventory item');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('supplement_id');
            $table->dropConstrainedForeignId('plant_id');
        });
    }
};
