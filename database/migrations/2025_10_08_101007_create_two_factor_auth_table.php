<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('two_factor_auth', function (Blueprint $table) {
            $table->id(); // auto increment id
            $table->unsignedBigInteger('user_id'); // verwijzing naar user
            $table->string('channel', 255); // bv 'email' of 'sms'
            $table->string('code', 255); // gehashte code
            $table->timestamp('expires_at'); // wanneer de code verloopt
            $table->timestamp('verified_at')->nullable(); // wanneer bevestigd
            $table->timestamp('used_at')->nullable(); // wanneer gebruikt
            $table->timestamps(); // created_at en updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('two_factor_auth');
    }
};
