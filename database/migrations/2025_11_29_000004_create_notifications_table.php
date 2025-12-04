<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_type_id')
                ->constrained('notification_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('References the type of notification');
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
                ->comment('References the user who receives the notification');
            $table->string('title')
                ->comment('Title of the notification');
            $table->text('content')
                ->comment('Content of the notification');
            $table->boolean('is_read')
                ->default(false)
                ->comment('Indicates if the notification has been read');
            $table->timestamp('read_at')
                ->nullable()
                ->comment('Timestamp when the notification was read');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
