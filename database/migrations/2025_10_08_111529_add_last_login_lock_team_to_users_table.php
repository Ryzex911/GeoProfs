<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('role_id');
            $table->timestamp('lock_at')->nullable()->after('last_login_at');
            $table->unsignedBigInteger('team_id')->nullable()->after('lock_at');

            // Optioneel: foreign key naar teams tabel als je die hebt
            // $table->foreign('team_id')->references('id')->on('teams')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'lock_at', 'team_id']);
        });
    }
};
