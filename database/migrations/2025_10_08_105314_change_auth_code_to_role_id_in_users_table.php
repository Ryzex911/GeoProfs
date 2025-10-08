<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // auth_code verwijderen
            $table->dropColumn('auth_code');

            // role_id toevoegen
            $table->unsignedBigInteger('role_id')->nullable()->after('password');

            // optioneel: foreign key naar roles tabel
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // role_id verwijderen
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');

            // auth_code terugzetten
            $table->string('auth_code')->nullable()->after('password');
        });
    }
};
