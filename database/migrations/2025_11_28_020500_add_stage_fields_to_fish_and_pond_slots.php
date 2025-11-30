<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fish', function (Blueprint $table): void {
            $table->unsignedInteger('egg_stage_seconds')->default(120)->after('feedings_per_day');
            $table->unsignedInteger('juvenile_stage_seconds')->default(60)->after('egg_stage_seconds');
            $table->unsignedInteger('adult_stage_seconds')->default(180)->after('juvenile_stage_seconds');
        });

        Schema::table('pond_slots', function (Blueprint $table): void {
            $table->unsignedInteger('stage_progress_seconds')->default(0)->after('stage_started_at');
            $table->unsignedInteger('stage_duration_seconds')->default(0)->after('stage_progress_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('pond_slots', function (Blueprint $table): void {
            $table->dropColumn(['stage_progress_seconds', 'stage_duration_seconds']);
        });

        Schema::table('fish', function (Blueprint $table): void {
            $table->dropColumn(['egg_stage_seconds', 'juvenile_stage_seconds', 'adult_stage_seconds']);
        });
    }
};
