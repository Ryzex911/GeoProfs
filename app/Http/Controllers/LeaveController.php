<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
// overzicht (Inertia)
public function index(): Response
{
$user = Auth::user();

    $leaveRequests = LeaveRequest::where('employee_id', $user->id)
        ->paginate(15);

    return Inertia::render('Requests/Index', [
        'leaveRequests' => $leaveRequests,
    ]);
}

// Return the plain blade dashboard (frontend-only view)
public function dashboard()
{
    return view('Requests.request-dashboard');
}

// Store via AJAX / JSON (frontend stuurt from/to as datetime-local strings)
public function store(Request $request)
{
    $user = Auth::user();

    // Je frontend stuurt 'type', 'from', 'to', 'reason' en optioneel 'note'
    $data = $request->validate([
        'type' => ['required', 'in:TVT,Vakantie,Anders'],
        'from' => ['required', 'date'],
        'to'   => ['required', 'date', 'after_or_equal:from'],
        'reason' => ['nullable', 'string', 'max:500'],
        'note' => ['nullable', 'string', 'max:1000'],
    ]);

    // Parse naar Carbon
    $start = Carbon::parse($data['from'])->startOfDay();
    $end = Carbon::parse($data['to'])->endOfDay();

    if ($end->lt($start)) {
        return response()->json(['message' => 'Einddatum mag niet vóór startdatum zijn.'], 422);
    }

    $today = Carbon::today();

    // Specifieke regels: Vakantie minimaal 7 dagen van tevoren
    if ($data['type'] === 'Vakantie') {
        $daysUntil = $today->diffInDays($start, false);
        if ($daysUntil < 7) {
            return response()->json(['message' => 'Vakantie moet minimaal 7 dagen van tevoren worden aangevraagd.'], 422);
        }
    }

    // Maak het record aan. In DB start_date/end_date zijn DATE-velden: sla als Y-m-d op.
    $leaveRequest = LeaveRequest::create([
        'employee_id' => $user->id,
        'manager_id' => $user->manager_id ?? null,
        'type' => $data['type'],
        'reason' => $data['reason'] ?? null,
        'start_date' => $start->toDateString(),
        'end_date' => $end->toDateString(),
        'status' => 'ingediend',
        'submitted_at' => now(),
        'notification_sent' => false,
    ]);

    Log::info('Verlofaanvraag ingediend via dashboard', ['user_id' => $user->id, 'leave_request_id' => $leaveRequest->id]);

    return response()->json(['success' => true, 'leave_request' => $leaveRequest], 201);
}

public function cancel(LeaveRequest $leaveRequest): RedirectResponse
{
    $user = Auth::user();
    if ($leaveRequest->employee_id !== $user->id) {
        abort(403, 'Unauthorized');
    }

    if (!in_array($leaveRequest->status, ['ingediend', 'goedgekeurd'])) {
        return back()->withErrors(['status' => 'Deze aanvraag kan niet meer worden geannuleerd.']);
    }

    $leaveRequest->update([
        'status' => 'geannuleerd',
        'canceled_at' => now(),
    ]);

    Log::info('Verlofaanvraag geannuleerd', ['user_id' => $user->id, 'leave_request_id' => $leaveRequest->id]);

    return redirect()->route('leave-requests.index')->with('success', 'Verlofaanvraag geannuleerd.');
}
}
