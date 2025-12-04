<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')
                ->unique()
                ->comment('Unique slug identifier for the notification type');
            $table->string('name')
                ->comment('Name of the notification type');
            $table->string('default_title')
                ->nullable()
                ->comment('Default title for notifications of this type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_types');
    }
};
