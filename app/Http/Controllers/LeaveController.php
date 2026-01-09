<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequestRequest;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class LeaveController extends Controller
{
// overzicht (Inertia)
    public function index()
    {
        $user = Auth::user();

        $Requests = LeaveRequest::where('employee_id', $user->id)
            ->paginate(15);

        return view('leave.index', compact('Requests'));
    }

// Toon dashboard view (blade)
    public function dashboard()
    {
        $leaveTypes = \App\Models\LeaveType::all();
        return view('Requests.request-dashboard', compact('leaveTypes'));
    }

    // Opslaan via POST (AJAX of formulier)
    public function store(StoreLeaveRequestRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        // Verloftype
        $leaveType = null;
        if (!empty($data['leave_type_id'])) {
            $leaveType = \App\Models\LeaveType::find($data['leave_type_id']);
        }
        if (!$leaveType) {
            return response()->json(['message' => 'Ongeldig verloftype.'], 422);
        }

        // Input: accepteer beide varianten
        $startInput = $data['start_date'] ?? $data['from'] ?? null;
        $endInput   = $data['end_date']   ?? $data['to']   ?? null;

        if (!$startInput || !$endInput) {
            return response()->json(['message' => 'Start- en einddatum zijn vereist.'], 422);
        }

        // ✅ definieer $start en $end altijd
        $start = Carbon::parse($startInput);
        $end   = Carbon::parse($endInput);

        if ($end->lte($start)) {
            return response()->json(['message' => '"Tot" moet na "Van" zijn.'], 422);
        }

        // reason: als je FormRequest reason required maakt, komt dit altijd mee
        $reason = $data['reason'] ?? null;

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'reason' => $reason,
            'start_date' => $start->toDateTimeString(),
            'end_date' => $end->toDateTimeString(),
            'proof' => null,
            'status' => LeaveRequest::STATUS_PENDING,
            'submitted_at' => now(),
            'notification_sent' => false,
        ]);

        return response()->json(['success' => true, 'leave_request' => $leaveRequest], 201);
    }


    public function destroy(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();

        if ($leaveRequest->employee_id !== $user->id) {
            abort(403, 'Geen toegang');
        }

        if ($leaveRequest->status !== 'geannuleerd') {
            return redirect()->back();
        }

        // Soft delete
        $leaveRequest->delete();

        return redirect()->route('leave-requests.index')
            ->with('success', 'Verlofaanvraag verwijderd.');
    }


    // Annuleer een verlofaanvraag
    public function cancel(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();

        // Beveiliging: alleen eigen aanvraag
        if ($leaveRequest->employee_id !== $user->id) {
            abort(403, 'Geen toegang');
        }

        // Alleen annuleren als status ingediend is
        if ($leaveRequest->status !== 'ingediend') {
            return redirect()->back();
        }

        $leaveRequest->status = 'geannuleerd';
        $leaveRequest->save();

        return redirect()->back()
            ->with('success', 'Verlofaanvraag is geannuleerd.');
    }

// Dashboard overzicht (KPI's)
    public function dashboardOverview()
    {
        $user = Auth::user();

        // Aantal lopende aanvragen van deze gebruiker
        $lopendeAanvragen = LeaveRequest::where('employee_id', $user->id)
            ->where('status', 'ingediend')
            ->count();

        return view('requests.dashboard', compact('lopendeAanvragen'));
    }


}
