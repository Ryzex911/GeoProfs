<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->buildFilteredQuery($request);

        $logs = $query
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('audit.index', compact('logs'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->buildFilteredQuery($request)->orderByDesc('created_at');

        $filename = 'audit-logs-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');

            // CSV header
            fputcsv($out, [
                'created_at',
                'log_type',
                'action',
                'actor_id',
                'actor_name',
                'actor_roles',
                'auditable_type',
                'auditable_id',
                'description',
                'ip',
                'method',
                'url',
                'request_id',
                'old_values',
                'new_values',
            ]);

            // Chunked export (werkt ook bij veel records)
            $query->chunk(1000, function ($rows) use ($out) {
                foreach ($rows as $log) {
                    $actorName = $log->user?->name ?? $log->user?->email ?? null;

                    fputcsv($out, [
                        optional($log->created_at)->format('Y-m-d H:i:s') ?? (string) $log->created_at,
                        $log->log_type,
                        $log->action,
                        $log->actor_id ?? $log->user_id,
                        $actorName,
                        $log->actor_roles,
                        $log->auditable_type ?? $log->entity,
                        $log->auditable_id ?? $log->entity_id,
                        $log->description,
                        $log->ip ?? $log->ip_address,
                        $log->method,
                        $log->url,
                        $log->request_id,
                        json_encode($log->old_values, JSON_UNESCAPED_UNICODE),
                        json_encode($log->new_values, JSON_UNESCAPED_UNICODE),
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Bouw de query met filters uit de request.
     */
    private function buildFilteredQuery(Request $request)
    {
        $q = AuditLog::query()->with('user');

        // type (security/audit)
        if ($request->filled('log_type')) {
            $q->where('log_type', $request->string('log_type'));
        }

        // action contains
        if ($request->filled('action')) {
            $q->where('action', 'like', '%' . $request->string('action') . '%');
        }

        // actor id
        if ($request->filled('actor_id')) {
            $actorId = (int) $request->input('actor_id');
            // support oud schema (user_id) en nieuw (actor_id)
            $q->where(function ($sub) use ($actorId) {
                $sub->where('actor_id', $actorId)->orWhere('user_id', $actorId);
            });
        }

        // actor name/email (via relatie)
        if ($request->filled('actor')) {
            $term = $request->string('actor');
            $q->whereHas('user', function ($u) use ($term) {
                $u->where('name', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%');
            });
        }

        // auditable type + id
        if ($request->filled('auditable_type')) {
            $q->where('auditable_type', $request->string('auditable_type'));
        }
        if ($request->filled('auditable_id')) {
            $q->where('auditable_id', (int) $request->input('auditable_id'));
        }

        // ip
        if ($request->filled('ip')) {
            $ip = $request->string('ip');
            $q->where(function ($sub) use ($ip) {
                $sub->where('ip', 'like', '%' . $ip . '%')
                    ->orWhere('ip_address', 'like', '%' . $ip . '%');
            });
        }

        // method
        if ($request->filled('method')) {
            $q->where('method', strtoupper($request->string('method')));
        }

        // date range
        if ($request->filled('from')) {
            $q->where('created_at', '>=', $request->date('from')->startOfDay());
        }
        if ($request->filled('to')) {
            $q->where('created_at', '<=', $request->date('to')->endOfDay());
        }

        return $q;
    }
}
