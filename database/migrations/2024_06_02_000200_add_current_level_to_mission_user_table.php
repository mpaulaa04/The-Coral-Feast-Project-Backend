<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mission_user', function (Blueprint $table): void {
            $table->unsignedTinyInteger('current_level')->default(1)->after('progress');
        });
    }

    public function down(): void
    {
        Schema::table('mission_user', function (Blueprint $table): void {
            $table->dropColumn('current_level');
        });
    }
};
