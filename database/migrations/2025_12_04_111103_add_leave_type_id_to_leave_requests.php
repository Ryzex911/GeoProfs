<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Voeg leave_type_id tijdelijk toe (nullable)
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('leave_type_id')->nullable()->after('employee_id');
        });

        // 2) Haal mapping van leave_types (name => id)
        $types = DB::table('leave_types')->pluck('id','name')->toArray();

        // 3) Update bestaande leave_requests op basis van de oude 'type' kolom
        // We proberen de namen precies te matchen; als jouw enum andere hoofdletters heeft, pas aan.
        if (!empty($types)) {
            foreach ($types as $name => $id) {
                DB::table('leave_requests')
                    ->where('type', $name)
                    ->update(['leave_type_id' => $id]);
            }

            // fallback: alle rijen zonder leave_type_id krijgen 'Anders' als die bestaat
            if (isset($types['Anders'])) {
                DB::table('leave_requests')
                    ->whereNull('leave_type_id')
                    ->update(['leave_type_id' => $types['Anders']]);
            }
        }

        // 4) Maak leave_type_id NOT NULL (optioneel; alleen als alle records zijn ingevuld)
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('leave_type_id')->nullable(false)->change();
        });

        // 5) Verwijder de oude enum-kolom 'type'
        if (Schema::hasColumn('leave_requests', 'type')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        // 6) Maak foreign key
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        // rollback: verwijder FK en leave_type_id, voeg oude type-kolom terug
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['leave_type_id']);
            $table->dropColumn('leave_type_id');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('type')->nullable()->after('employee_id');
        });

        // probeer type terug te zetten op basis van leave_types mapping (voor zover mogelijk)
        $mapping = DB::table('leave_types')->pluck('name','id')->toArray();
        foreach ($mapping as $id => $name) {
            DB::table('leave_requests')->where('leave_type_id', $id)->update(['type' => $name]);
        }
    }
};
