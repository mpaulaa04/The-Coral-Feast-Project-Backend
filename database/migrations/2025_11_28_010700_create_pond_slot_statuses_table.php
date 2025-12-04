<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pond_slot_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')
                ->unique()
                ->comment('Unique name of the pond slot status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pond_slot_statuses');
    }
};
