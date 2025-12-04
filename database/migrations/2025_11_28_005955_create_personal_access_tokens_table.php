<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable'); // Laravel morphs: tokenable_id, tokenable_type
            $table->text('name')
                ->comment('Name of the access token');
            $table->string('token', 64)
                ->unique()
                ->comment('Unique token string');
            $table->text('abilities')
                ->nullable()
                ->comment('JSON array of token abilities');
            $table->timestamp('last_used_at')
                ->nullable()
                ->comment('Timestamp when the token was last used');
            $table->timestamp('expires_at')
                ->nullable()
                ->index()
                ->comment('Expiration timestamp for the token');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
