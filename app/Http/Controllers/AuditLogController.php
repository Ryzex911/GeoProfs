<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('audit.index', compact('logs'));
    }
}
