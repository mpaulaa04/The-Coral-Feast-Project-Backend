<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pond_slots', function (Blueprint $table) {
            if (! Schema::hasColumn('pond_slots', 'has_water_quality_issue')) {
                $table->boolean('has_water_quality_issue')->default(false)->after('has_temperature_issue');
            }

            if (! Schema::hasColumn('pond_slots', 'last_cleaned_at')) {
                $table->timestamp('last_cleaned_at')->nullable()->after('last_ph_adjusted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pond_slots', function (Blueprint $table) {
            if (Schema::hasColumn('pond_slots', 'last_cleaned_at')) {
                $table->dropColumn('last_cleaned_at');
            }

            if (Schema::hasColumn('pond_slots', 'has_water_quality_issue')) {
                $table->dropColumn('has_water_quality_issue');
            }
        });
    }
};
