<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pond_slots', function (Blueprint $table): void {
            if (! Schema::hasColumn('pond_slots', 'plant_effect_state')) {
                $table->json('plant_effect_state')->nullable()->after('plant_placed_at');
            }

            if (! Schema::hasColumn('pond_slots', 'plant_effect_expires_at')) {
                $table->timestamp('plant_effect_expires_at')->nullable()->after('plant_effect_state');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pond_slots', function (Blueprint $table): void {
            if (Schema::hasColumn('pond_slots', 'plant_effect_expires_at')) {
                $table->dropColumn('plant_effect_expires_at');
            }

            if (Schema::hasColumn('pond_slots', 'plant_effect_state')) {
                $table->dropColumn('plant_effect_state');
            }
        });
    }
};
