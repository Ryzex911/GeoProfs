<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LeaveController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $leaves = $user->leaves()->orderByDesc('start_date')->get();

        return Inertia::render('Leaves/Index', [
            'leaves' => $leaves,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Leaves/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:regular,sick,other'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->startOfDay();

        if ($end->lt($start)) {
            return back()->withErrors(['end_date' => 'Einddatum mag niet vóór startdatum zijn.'])->onlyInput();
        }

        $today = Carbon::today();

        // sick: if start is today, must be before 09:00
        if ($data['type'] === 'sick' && $start->isSameDay($today)) {
            if (Carbon::now()->hour >= 9) {
                return back()->withErrors(['type' => 'Ziekmelding moet vóór 09:00 worden ingediend.'])->onlyInput();
            }
        }

        // regular: minimum 7 days notice
        if ($data['type'] === 'regular') {
            $daysUntilStart = $today->diffInDays($start, false);
            if ($daysUntilStart < 7) {
                return back()->withErrors(['start_date' => 'Regulier verlof moet minimaal 7 dagen van tevoren worden aangevraagd.'])->onlyInput();
            }
        }

        $daysRequested = $start->diffInDays($end) + 1;

        $user = $request->user();
        if (isset($user->remaining_leave) && $user->remaining_leave !== null) {
            if ($daysRequested > $user->remaining_leave) {
                return back()->withErrors(['type' => 'Je hebt niet genoeg resterend verlof.'])->onlyInput();
            }
        }

        $leave = Leave::create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'reason' => $data['reason'] ?? null,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'days' => $daysRequested,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        // notify manager/HR: fallback to log + simple mail to configured address
        Log::info('Leave requested', ['user_id' => $user->id, 'leave_id' => $leave->id, 'type' => $leave->type, 'days' => $leave->days]);

        try {
            $hr = config('mail.hr_address', config('mail.from.address'));
            if ($hr) {
                Mail::raw("Nieuwe verlofaanvraag van {$user->email} ({$leave->days} dag(en)).", function ($m) use ($hr) {
                    $m->to($hr)->subject('Nieuwe verlofaanvraag');
                });
            }
        } catch (\Throwable $e) {
            Log::error('Leave notification failed: ' . $e->getMessage());
        }

        return redirect()->route('leaves.index')->with('success', 'Verlofaanvraag is ingediend.');
    }

    public function show(Leave $leave): Response
    {
        $user = auth()->user();
        if ($leave->user_id !== $user->id) {
            abort(403);
        }

        return Inertia::render('Leaves/Show', [
            'leave' => $leave,
        ]);
    }

    public function cancel(Leave $leave): RedirectResponse
    {
        $user = auth()->user();

        if ($leave->user_id !== $user->id) {
            abort(403);
        }

        if ($leave->status === 'approved') {
            return back()->withErrors(['leave' => 'Een goedgekeurde aanvraag kan niet worden geannuleerd.']);
        }

        $leave->status = 'cancelled';
        $leave->cancelled_at = now();
        $leave->save();

        Log::info('Leave cancelled', ['user_id' => $user->id, 'leave_id' => $leave->id]);

        return redirect()->route('leaves.index')->with('success', 'Verlofaanvraag geannuleerd.');
    }
}
