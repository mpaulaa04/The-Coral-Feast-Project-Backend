<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pond_slots', function (Blueprint $table): void {
            $table->foreignId('plant_id')->nullable()->after('fish_id')->constrained('plants')->nullOnDelete();
            $table->timestamp('plant_placed_at')->nullable()->after('plant_id');
        });
    }

    public function down(): void
    {
        Schema::table('pond_slots', function (Blueprint $table): void {
            $table->dropColumn('plant_placed_at');
            $table->dropConstrainedForeignId('plant_id');
        });
    }
};
