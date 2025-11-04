<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Droppen alleen als kolom echt bestaat
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Terugdraaien: kolom terugzetten als hij ontbreekt
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->nullable();
            }
        });
    }
};
