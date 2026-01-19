<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {

            // Check en drop foreign key eerst
            if (Schema::hasColumn('leave_requests', 'manager_id')) {

                // Foreign key droppen (naam is standaard: leave_requests_manager_id_foreign)
                $table->dropForeign(['manager_id']);

                // Kolom droppen
                $table->dropColumn('manager_id');
            }

            // Voeg proof toe
            if (!Schema::hasColumn('leave_requests', 'proof')) {
                $table->string('proof', 255)->nullable()->after('end_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {

            // Manager opnieuw toevoegen
            if (!Schema::hasColumn('leave_requests', 'manager_id')) {
                $table->unsignedBigInteger('manager_id')->nullable();

                // Foreign key opnieuw toevoegen
                $table
                    ->foreign('manager_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            }

            // Proof verwijderen
            if (Schema::hasColumn('leave_requests', 'proof')) {
                $table->dropColumn('proof');
            }
        });
    }
};
