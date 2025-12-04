<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ponds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->comment('References the user who owns the pond');
            $table->string('name')
                ->comment('Name of the pond');
            $table->string('status')
                ->default('active')
                ->comment('Current status of the pond');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ponds');
    }
};
