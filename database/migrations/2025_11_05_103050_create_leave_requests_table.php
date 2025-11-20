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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->enum('type', ['TVT', 'Vakantie', 'Anders']);
            $table->string('reason', 255)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['ingediend', 'goedgekeurd', 'afgewezen', 'geannuleerd']);
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->boolean('notification_sent')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
