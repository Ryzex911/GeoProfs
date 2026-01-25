<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {

            if (!Schema::hasColumn('audit_logs', 'log_type')) {
                $table->string('log_type', 20)->default('audit')->after('id');
            }

            // actor_id i.p.v user_id (we houden user_id voorlopig voor backward compatibility)
            if (!Schema::hasColumn('audit_logs', 'actor_id')) {
                $table->foreignId('actor_id')->nullable()
                    ->after('action')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('audit_logs', 'actor_roles')) {
                $table->string('actor_roles')->nullable()->after('actor_id');
            }

            if (!Schema::hasColumn('audit_logs', 'auditable_type')) {
                $table->string('auditable_type')->nullable()->after('entity_id');
            }

            if (!Schema::hasColumn('audit_logs', 'auditable_id')) {
                $table->unsignedBigInteger('auditable_id')->nullable()->after('auditable_type');
            }

            if (!Schema::hasColumn('audit_logs', 'description')) {
                $table->text('description')->nullable()->after('auditable_id');
            }

            if (!Schema::hasColumn('audit_logs', 'old_values')) {
                $table->json('old_values')->nullable()->after('description');
            }

            if (!Schema::hasColumn('audit_logs', 'new_values')) {
                $table->json('new_values')->nullable()->after('old_values');
            }

            // context velden
            if (!Schema::hasColumn('audit_logs', 'ip')) {
                $table->string('ip', 45)->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip');
            }
            if (!Schema::hasColumn('audit_logs', 'url')) {
                $table->text('url')->nullable()->after('user_agent');
            }
            if (!Schema::hasColumn('audit_logs', 'method')) {
                $table->string('method', 10)->nullable()->after('url');
            }
            if (!Schema::hasColumn('audit_logs', 'request_id')) {
                $table->uuid('request_id')->nullable()->after('method');
            }
        });

        // Backfill (kopieer oude velden naar nieuwe) – veilig om meermaals te draaien
        DB::table('audit_logs')
            ->whereNull('actor_id')
            ->whereNotNull('user_id')
            ->update(['actor_id' => DB::raw('user_id')]);

        DB::table('audit_logs')
            ->whereNull('auditable_type')
            ->whereNotNull('entity')
            ->update(['auditable_type' => DB::raw('entity')]);

        DB::table('audit_logs')
            ->whereNull('auditable_id')
            ->whereNotNull('entity_id')
            ->update(['auditable_id' => DB::raw('entity_id')]);

        DB::table('audit_logs')
            ->whereNull('ip')
            ->whereNotNull('ip_address')
            ->update(['ip' => DB::raw('ip_address')]);
    }

    public function down(): void
    {
        // Down laten we bewust leeg of alleen de nieuwe kolommen droppen als je dat wilt.
        // In audit systemen is "down" vaak niet gewenst.
    }
};
