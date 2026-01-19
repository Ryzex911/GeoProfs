<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // 1) Als je tijd wilt ondersteunen: date -> datetime
            // Hiervoor heb je vaak doctrine/dbal nodig (zie stap 3)
            $table->dateTime('start_date')->change();
            $table->dateTime('end_date')->change();

            // 2) Zorg dat status een standaardwaarde heeft (consistent met je model constants)
            $table->string('status')->default('pending')->change();

            // 3) Extra kolommen die je model al verwacht (alleen toevoegen als ze nog niet bestaan)
            if (!Schema::hasColumn('leave_requests', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('leave_requests', 'canceled_at')) {
                $table->timestamp('canceled_at')->nullable()->after('approved_by');
            }
        });

        // 4) Data migreren: oude Nederlandse statuswaarden omzetten naar je constants
        DB::table('leave_requests')->where('status', 'ingediend')->update(['status' => 'pending']);
        DB::table('leave_requests')->where('status', 'geannuleerd')->update(['status' => 'canceled']);
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // terug naar date (als je dit echt wilt)
            $table->date('start_date')->change();
            $table->date('end_date')->change();

            // status terug zonder default (optioneel)
            $table->string('status')->default(null)->change();

            // kolommen verwijderen alleen als ze in up zijn toegevoegd
            if (Schema::hasColumn('leave_requests', 'approved_by')) {
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('leave_requests', 'canceled_at')) {
                $table->dropColumn('canceled_at');
            }
        });

        // status terugzetten (optioneel)
        DB::table('leave_requests')->where('status', 'pending')->update(['status' => 'ingediend']);
        DB::table('leave_requests')->where('status', 'canceled')->update(['status' => 'geannuleerd']);
    }
};
