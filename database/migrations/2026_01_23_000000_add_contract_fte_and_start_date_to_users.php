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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'contract_fte')) {
                $table->float('contract_fte')->default(1.0)->after('password');
            }
            if (!Schema::hasColumn('users', 'start_date')) {
                $table->date('start_date')->nullable()->after('contract_fte');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('users', 'contract_fte')) {
                $table->dropColumn('contract_fte');
            }
        });
    }
};
