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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('year');
            $table->decimal('remaining_hours', 8, 2)->default(200); // 25 days * 8 hours
            $table->decimal('carryover_hours', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'year']); // One balance per user per year
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
