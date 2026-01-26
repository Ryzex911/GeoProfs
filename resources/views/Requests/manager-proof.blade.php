{{-- resources/views/Requests/manager-proof.blade.php --}}
    <!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Bewijs bekijken — GeoProfs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root{
            --bg:#f5f6fb;
            --card:#ffffff;
            --text:#0f172a;
            --muted:#64748b;
            --line:#e8eaf2;
            --chip:#eef2ff;
            --primary:#1d4ed8;
            --primary-soft:#e8efff;
            --shadow:0 10px 30px rgba(15,23,42,.06);
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial;
            background:var(--bg);
            color:var(--text);
        }
        a{color:inherit}
        .container{max-width:1100px; margin:0 auto; padding:18px}
        .topbar{
            position:sticky; top:0; z-index:20;
            background:rgba(245,246,251,.9);
            backdrop-filter: blur(10px);
            border-bottom:1px solid var(--line);
        }
        .topbar-inner{
            max-width:1100px; margin:0 auto; padding:14px 18px;
            display:flex; align-items:center; justify-content:space-between; gap:12px;
        }
        .brand{display:flex; align-items:center; gap:10px; font-weight:800}
        .logo-dot{width:10px; height:10px; border-radius:999px; background:var(--primary)}
        .btn{
            display:inline-flex; align-items:center; justify-content:center; gap:8px;
            border-radius:12px; border:1px solid var(--line);
            background:var(--card);
            padding:10px 12px;
            text-decoration:none; font-weight:700; font-size:14px;
            box-shadow: 0 2px 0 rgba(15,23,42,.02);
        }
        .btn-primary{
            border-color:transparent;
            background:var(--primary);
            color:#fff;
        }
        .btn-ghost{
            background:transparent;
        }
        .page{
            margin-top:16px;
            background:var(--card);
            border:1px solid var(--line);
            border-radius:16px;
            box-shadow:var(--shadow);
            padding:16px;
        }
        .header{
            display:flex; align-items:flex-start; justify-content:space-between; gap:12px;
            flex-wrap:wrap;
        }
        .title{margin:0; font-size:20px; font-weight:800}
        .meta{
            margin-top:10px;
            display:grid;
            grid-template-columns: 1fr;
            gap:10px;
        }
        .meta-row{
            display:flex; flex-wrap:wrap; gap:10px;
        }
        .pill{
            display:inline-flex; align-items:center; gap:8px;
            padding:8px 12px;
            border-radius:999px;
            background:var(--chip);
            border:1px solid var(--line);
            color:#1e293b;
            font-size:13px;
            font-weight:700;
            white-space:nowrap;
        }
        .kv{
            display:grid;
            grid-template-columns: 140px 1fr;
            gap:8px;
            padding:10px 12px;
            border:1px solid var(--line);
            border-radius:14px;
            background:#fbfcff;
        }
        .k{color:var(--muted); font-weight:700; font-size:13px}
        .v{font-weight:700; font-size:13px; color:#111827; overflow-wrap:anywhere}
        .grid{
            margin-top:14px;
            display:grid;
            grid-template-columns: 1fr;
            gap:12px;
        }
        @media (min-width: 980px){
            .grid{grid-template-columns: 1.35fr .65fr;}
        }
        .card{
            border:1px solid var(--line);
            border-radius:16px;
            background:var(--card);
            padding:14px;
        }
        .card h3{
            margin:0 0 10px;
            font-size:14px;
            color:#0f172a;
            font-weight:800;
        }
        .muted{color:var(--muted); font-size:13px}
        .actions{display:flex; flex-wrap:wrap; gap:10px; margin-top:10px}
        .preview{
            margin-top:12px;
            border:1px solid var(--line);
            border-radius:14px;
            overflow:hidden;
            background:#f8fafc;
        }
        .preview img, .preview video{width:100%; height:auto; display:block}
        .preview iframe{width:100%; height:520px; border:0}
        .empty{
            padding:14px;
            border:1px dashed var(--line);
            border-radius:14px;
            background:#fbfcff;
        }
        .small{font-size:12px; color:var(--muted)}
        .mono{font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Courier New", monospace}
    </style>
</head>
<body>
@php
    /** @var \App\Models\LeaveRequest $leaveRequest */

    $employeeName  = $leaveRequest->employee?->name ?? '—';
    $employeeEmail = $leaveRequest->employee?->email ?? '—';
    $typeName      = $leaveRequest->leaveType?->name ?? '—';

    // Proof: alleen bestandspad (storage). Als je toch ooit URL’s in proof hebt: we supporten het netjes.
    $proofRaw = trim((string)($leaveRequest->proof ?? ''));
    $proofUrl = null;
    $isExternal = false;

    if ($proofRaw !== '') {
        if (preg_match('~^(https?://|www\.)~i', $proofRaw)) {
            $proofUrl = preg_match('~^www\.~i', $proofRaw) ? 'https://'.$proofRaw : $proofRaw;
            $isExternal = true;
        } else {
            $proofUrl = asset('storage/' . ltrim($proofRaw, '/'));
        }
    }

    $ext = (!$isExternal && $proofRaw !== '') ? strtolower(pathinfo($proofRaw, PATHINFO_EXTENSION)) : null;
    $isImage = $ext && in_array($ext, ['jpg','jpeg','png','gif','webp'], true);
    $isPdf   = ($ext === 'pdf');
    $isVideo = $ext && in_array($ext, ['mp4','webm','ogg'], true);

    $proofState = $proofUrl ? 'Aanwezig' : '—';
@endphp

<div class="topbar">
    <div class="topbar-inner">
        <div class="brand">
            <span class="logo-dot"></span>
            <span>GeoProfs</span>
        </div>


    </div>
</div>

<div class="container">
    <section class="page">
        <div class="header">
            <div>
                <h1 class="title">Bewijs bekijken</h1>

                <div class="meta">

                </div>
            </div>
        </div>

        <div class="grid">
            <div class="card">
                <h3>Bestand</h3>

                @if(!$proofUrl)
                    <div class="empty">
                        <div style="font-weight:800;">—</div>
                        <div class="small">Geen bewijsbestand opgeslagen voor deze aanvraag.</div>
                    </div>
                @else
                    <div class="kv">
                        <div class="k">Pad</div>
                        <div class="v mono">{{ $proofRaw }}</div>
                    </div>

                    <div class="actions">
                        <a class="btn btn-primary" href="{{ $proofUrl }}" target="_blank" rel="noopener">Openen</a>

                        {{-- Download alleen zinvol voor storage files --}}
                        @if(!$isExternal)
                            <a class="btn" href="{{ $proofUrl }}" download>Download</a>
                        @endif
                    </div>

                    @if($isImage)
                        <div class="preview">
                            <img src="{{ $proofUrl }}" alt="Bewijs afbeelding">
                        </div>
                    @elseif($isPdf)
                        <div class="preview">
                            <iframe src="{{ $proofUrl }}"></iframe>
                        </div>
                    @elseif($isVideo)
                        <div class="preview">
                            <video controls src="{{ $proofUrl }}"></video>
                        </div>
                    @else
                        <div class="empty" style="margin-top:12px;">
                            <div style="font-weight:800;">Preview niet beschikbaar</div>
                            <div class="small">Gebruik “Openen” (en “Download” indien beschikbaar).</div>
                        </div>
                    @endif
                @endif
            </div>

            <div class="card">
                <h3>Samenvatting</h3>

                <div class="kv">
                    <div class="k">Medewerker</div>
                    <div class="v">{{ $employeeName }}</div>
                </div>

                <div class="kv" style="margin-top:10px;">
                    <div class="k">Verlof type</div>
                    <div class="v">{{ $typeName }}</div>
                </div>

                <div class="kv" style="margin-top:10px;">
                    <div class="k">Bewijs</div>
                    <div class="v">{{ $proofState }}</div>
                </div>

                <div class="small" style="margin-top:10px;">
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a class="btn btn-ghost" href="{{ route('manager.requests.index') }}">Terug</a>
                </div>
            </div>

        </div>
    </section>
</div>

</body>
</html>
