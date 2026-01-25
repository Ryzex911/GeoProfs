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
        if (!Schema::hasColumn('users', 'contract_fte')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('contract_fte', 3, 2)->default(1.0)->after('email');
            });
        }

        if (!Schema::hasColumn('users', 'start_date')) {
            Schema::table('users', function (Blueprint $table) {
                $table->date('start_date')->nullable()->after('contract_fte');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'contract_fte')) {
                $table->dropColumn('contract_fte');
            }
            if (Schema::hasColumn('users', 'start_date')) {
                $table->dropColumn('start_date');
            }
        });
    }
};
