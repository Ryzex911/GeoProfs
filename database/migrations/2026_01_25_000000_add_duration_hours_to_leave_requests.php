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
        Schema::table('leave_requests', function (Blueprint $table) {
            // Voeg duration_hours toe als nullable decimal
            // Dit bevat het aantal uren voor een verlofaanvraag
            if (!Schema::hasColumn('leave_requests', 'duration_hours')) {
                $table->decimal('duration_hours', 8, 2)->nullable()->after('end_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('duration_hours');
        });
    }
};
