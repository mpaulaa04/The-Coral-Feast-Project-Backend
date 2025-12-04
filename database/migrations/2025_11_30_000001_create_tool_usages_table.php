<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->comment('References the user who used the tool');
            $table->string('tool_slug', 50)
                ->comment('Slug identifier for the tool');
            $table->unsignedBigInteger('usage_count')
                ->default(0)
                ->comment('Total number of times the tool was used by the user');
            $table->timestamp('last_used_at')
                ->nullable()
                ->comment('Timestamp of the last usage of the tool by the user');
            $table->timestamps();

            $table->unique(['user_id', 'tool_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_usages');
    }
};
