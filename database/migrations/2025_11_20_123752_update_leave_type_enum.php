<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('leave_requests')
            ->where('type', 'Ziek')
            ->update(['type' => 'Anders']);

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->enum('type', ['TVT', 'Vakantie', 'Anders'])
                ->default('Vakantie')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->enum('type', ['Ziek', 'Vakantie', 'Anders'])
                ->default('Vakantie')
                ->change();
        });
        DB::table('leave_requests')
            ->where('type', 'Anders')
            ->update(['type' => 'Ziek']);
    }
};
