<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('description')->nullable();
            $table->boolean('requires_proof')->default(false);
            $table->integer('max_days')->nullable();
            $table->timestamps();
        });

        // Vul standaardtypes (naam EXACT zoals we later matchen)
        DB::table('leave_types')->insert([
            ['name' => 'Vakantie', 'description' => 'Regulier verlof', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ziek',     'description' => 'Ziekmelding',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Anders',   'description' => 'Bijzonder verlof','created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
