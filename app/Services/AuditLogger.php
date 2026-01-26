<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLogger
{
    public function log(
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $logType = 'audit',
        ?string $description = null
    ): void {
        $user = auth()->user();
        $request = request();

        $requestId = $request?->attributes?->get('request_id') ?: (string) Str::uuid();

        $roles = null;
        if ($user) {
            // werkt als je User model een roles() relatie heeft
            try {
                $roles = method_exists($user, 'roles')
                    ? $user->roles()->pluck('name')->implode(',')
                    : null;
            } catch (\Throwable $e) {
                $roles = null;
            }
        }

        AuditLog::create([
            'log_type'       => $logType,
            'action'         => $action,
            'actor_id'       => $user?->id,
            'actor_roles'    => $roles,

            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id'   => $auditable?->getKey(),

            'description'    => $description,
            'old_values'     => $oldValues,
            'new_values'     => $newValues,

            'ip'             => $request?->ip(),
            'user_agent'     => $request?->userAgent(),
            'url'            => $request?->fullUrl(),
            'method'         => $request?->method(),
            'request_id'     => $requestId,
            'created_at'     => now(),
        ]);
    }
}
