<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('leave_requests', 'proof_link')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->string('proof_link', 2048)->nullable()->after('proof');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('leave_requests', 'proof_link')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropColumn('proof_link');
            });
        }
    }
};
