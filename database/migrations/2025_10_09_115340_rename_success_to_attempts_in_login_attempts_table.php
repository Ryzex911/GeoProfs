<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_attempts', function (Blueprint $table) {
            $table->renameColumn('success', 'attempts');
            $table->boolean('attempts')->change();
        });
    }

    public function down(): void
    {
        Schema::table('login_attempts', function (Blueprint $table) {
            $table->renameColumn('attempts', 'success');
            $table->boolean('success')->change();
        });
    }
};
