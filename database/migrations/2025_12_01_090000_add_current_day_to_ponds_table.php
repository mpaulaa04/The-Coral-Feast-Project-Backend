<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ponds', function (Blueprint $table): void {
            if (! Schema::hasColumn('ponds', 'current_day')) {
                $table->unsignedInteger('current_day')
                    ->default(1)
                    ->after('status')
                    ->comment('Tracks the current day of the pond');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ponds', function (Blueprint $table): void {
            if (Schema::hasColumn('ponds', 'current_day')) {
                $table->dropColumn('current_day');
            }
        });
    }
};
