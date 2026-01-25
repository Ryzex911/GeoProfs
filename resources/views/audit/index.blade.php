{{-- resources/views/audit/index.blade.php --}}
    <!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Audit logs</title>

    <style>
        :root{
            --bg:#0b1220;
            --card:#111a2e;
            --muted:#93a4c7;
            --text:#e6ecff;
            --line:rgba(255,255,255,.08);
            --chip:#1b2a4a;

            --field:#0f1930;
            --fieldFocus:rgba(255,255,255,.18);
        }

        /* ✅ native select/option dark rendering */
        html { color-scheme: dark; }

        *{box-sizing:border-box}
        body{
            margin:0;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Apple Color Emoji","Segoe UI Emoji";
            background: var(--bg);
            color: var(--text);
        }
        a{color:inherit}

        .container{max-width:1200px; margin:0 auto; padding:24px}
        .header{
            display:flex;
            align-items:flex-end;
            justify-content:space-between;
            gap:16px;
            margin-bottom:16px;
            flex-wrap:wrap; /* ✅ responsive */
        }
        .title{font-size:22px; font-weight:700; margin:0}
        .subtitle{color:var(--muted); margin:6px 0 0; font-size:13px}

        .card{
            background: var(--card);
            border:1px solid var(--line);
            border-radius:14px;
            padding:16px;
        }

        /* ✅ Filters responsive grid */
        .filters{
            margin-bottom:14px;
            padding:16px;
        }
        .filters-grid{
            display:grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap:10px;
            align-items:end;
        }
        @media (max-width: 900px){
            .filters-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 560px){
            .container{padding:16px}
            .filters-grid{ grid-template-columns: 1fr; }
        }

        .label{color:var(--muted); margin:0 0 6px; font-size:12px}
        .control{
            width:100%;
            padding:10px 12px;
            border-radius:10px;
            border:1px solid var(--line);
            background: var(--field);
            color: var(--text);
            outline:none;
        }
        .control:focus{
            border-color: var(--fieldFocus);
            box-shadow: 0 0 0 3px rgba(255,255,255,.06);
        }
        select.control{
            appearance:auto; /* keep native */
        }

        .actions{
            grid-column: span 4;
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }
        @media (max-width: 900px){
            .actions{ grid-column: span 2; }
        }
        @media (max-width: 560px){
            .actions{ grid-column: span 1; }
        }

        .tablewrap{overflow:auto; border-radius:12px; border:1px solid var(--line)}
        table{width:100%; border-collapse:collapse; min-width:980px; background:rgba(255,255,255,.02)}
        th,td{padding:10px 12px; border-bottom:1px solid var(--line); vertical-align:top; text-align:left; font-size:13px}
        th{color:var(--muted); font-weight:600; background:rgba(255,255,255,.03); position:sticky; top:0}
        tr:hover td{background:rgba(255,255,255,.03)}

        .chip{
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:3px 10px;
            border-radius:999px;
            background:var(--chip);
            border:1px solid var(--line);
            font-size:12px;
            color:var(--text);
            white-space:nowrap;
        }
        .btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            padding:10px 14px;
            border-radius:12px;
            border:1px solid var(--line);
            background:rgba(255,255,255,.02);
            color:var(--text);
            text-decoration:none;
            font-size:13px;
            cursor:pointer;
        }
        .btn:hover{background:rgba(255,255,255,.06)}
        .btn:active{transform:translateY(1px)}

        .muted{color:var(--muted)}
        .mono{font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace}

        details{margin-top:6px}
        summary{cursor:pointer; color:var(--muted)}
        pre{
            margin:10px 0 0;
            padding:10px;
            background:rgba(0,0,0,.25);
            border:1px solid var(--line);
            border-radius:10px;
            overflow:auto;
            max-height:220px;
        }

        .pagination{margin-top:14px}
        .pagination nav{display:flex; justify-content:center; flex-wrap:wrap; gap:6px}
        .pagination a, .pagination span{
            display:inline-block; padding:8px 10px;
            border:1px solid var(--line); border-radius:10px; text-decoration:none;
            color:var(--text); background:rgba(255,255,255,.02); font-size:13px;
        }
        .pagination span[aria-current="page"]{background:rgba(255,255,255,.08)}
    </style>
</head>

<body>
<div class="container">
    <div class="header">
        <div>
            <h1 class="title">Audit logs</h1>
            <p class="subtitle">
                Overzicht van acties (login/rollen/verlof/ziekte). Alleen-lezen.
            </p>
        </div>
        <div class="muted" style="font-size:12px;">
            Totaal op deze pagina: <span class="mono">{{ $logs->count() }}</span>
        </div>
    </div>

    <div class="card">

        {{-- Filters (simpel) --}}
        <form method="GET" class="card filters">
            <div class="filters-grid">

                <div>
                    <div class="label">Type</div>
                    <select name="log_type" class="control">
                        <option value="">Alle</option>
                        <option value="audit" {{ request('log_type')==='audit' ? 'selected' : '' }}>audit</option>
                        <option value="security" {{ request('log_type')==='security' ? 'selected' : '' }}>security</option>
                    </select>
                </div>

                <div>
                    <div class="label">Action bevat</div>
                    <input name="action" value="{{ request('action') }}" placeholder="leave_request. / auth." class="control">
                </div>

                <div>
                    <div class="label">User (naam/email)</div>
                    <input name="actor" value="{{ request('actor') }}" placeholder="zoek op naam of email" class="control">
                </div>

                <div style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <div class="label">Vanaf</div>
                        <input type="date" name="from" value="{{ request('from') }}" class="control">
                    </div>
                    <div style="flex:1;">
                        <div class="label">Tot</div>
                        <input type="date" name="to" value="{{ request('to') }}" class="control">
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn">Filter</button>

                    <a class="btn" href="{{ route('audit.export', request()->query()) }}">
                        Export CSV
                    </a>

                    <a class="btn" href="{{ route('audit.index') }}">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="tablewrap">
            <table>
                <thead>
                <tr>
                    <th style="width:170px;">Datum/tijd</th>
                    <th style="width:110px;">Type</th>
                    <th style="width:210px;">Action</th>
                    <th style="width:190px;">User</th>
                    <th style="width:140px;">IP</th>
                    <th>Details</th>
                </tr>
                </thead>

                <tbody>
                @forelse($logs as $log)
                    @php
                        $created = $log->created_at;
                        $createdStr = $created instanceof \Carbon\CarbonInterface
                            ? $created->format('Y-m-d H:i:s')
                            : (is_string($created) ? $created : '—');

                        $u = $log->user ?? null;
                        $userLabel = '—';
                        if ($u) {
                            $userLabel = $u->name ?? $u->email ?? ('User#'.$u->id);
                        } elseif (!empty($log->actor_id)) {
                            $userLabel = 'User#'.$log->actor_id;
                        } elseif (!empty($log->user_id)) {
                            $userLabel = 'User#'.$log->user_id;
                        }

                        $type = $log->log_type ?? 'audit';
                        $action = $log->action ?? '—';

                        $ip = $log->ip ?? $log->ip_address ?? '—';

                        $entity = $log->auditable_type ?? $log->entity ?? null;
                        $entityId = $log->auditable_id ?? $log->entity_id ?? null;

                        $old = $log->old_values ?? null;
                        $new = $log->new_values ?? null;

                        $url = $log->url ?? null;
                        $method = $log->method ?? null;
                        $ua = $log->user_agent ?? null;
                        $rid = $log->request_id ?? null;
                        $desc = $log->description ?? null;
                        $roles = $log->actor_roles ?? null;
                    @endphp

                    <tr>
                        <td class="mono">{{ $createdStr }}</td>
                        <td><span class="chip">{{ $type }}</span></td>
                        <td class="mono">{{ $action }}</td>

                        <td>
                            <div>{{ $userLabel }}</div>
                            @if(!empty($roles))
                                <div class="muted mono" style="margin-top:4px;">roles: {{ $roles }}</div>
                            @endif
                        </td>

                        <td class="mono">{{ $ip }}</td>

                        <td>
                            <div class="muted">
                                @if($entity)
                                    <span class="chip">{{ class_basename($entity) }}{{ $entityId ? (' #'.$entityId) : '' }}</span>
                                @endif
                                @if($method || $url)
                                    <span class="chip">{{ $method ?? '—' }}</span>
                                @endif
                                @if($url)
                                    <span class="mono">{{ $url }}</span>
                                @endif
                            </div>

                            <details>
                                <summary>Bekijk details</summary>

                                @if($desc)
                                    <div style="margin-top:8px;">
                                        <div class="muted">Omschrijving</div>
                                        <div>{{ $desc }}</div>
                                    </div>
                                @endif

                                <div style="margin-top:10px;">
                                    <div class="muted">Request context</div>
                                    <div class="mono" style="margin-top:6px;">
                                        request_id: {{ $rid ?? '—' }}<br>
                                        user_agent: {{ $ua ?? '—' }}
                                    </div>
                                </div>

                                <div style="margin-top:10px;">
                                    <div class="muted">Wijzigingen</div>

                                    @if($old || $new)
                                        <pre class="mono">{{ json_encode(['old' => $old, 'new' => $new], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @else
                                        <div class="muted" style="margin-top:6px;">Geen old/new values opgeslagen.</div>
                                    @endif
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">Geen audit logs gevonden.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $logs->links() }}
        </div>
    </div>
</div>
</body>
</html>
