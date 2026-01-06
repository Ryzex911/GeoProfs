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
    public function store(Request $request)
    {
        $user = Auth::user();
        // Accepteer 'leave_type_id' (int) of 'type' (naam). Geef voorkeur aan 'leave_type_id'.
        $data = $request->validate([
            'leave_type_id' => ['nullable', 'integer', 'exists:leave_types,id'],
            'type' => ['nullable', 'string', 'max:100'],
            // accepteer frontend veldnamen 'from'/'to' of 'start_date'/'end_date'
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        // Bepaal verloftype
        $leaveType = null;
        if (!empty($data['leave_type_id'])) {
            $leaveType = \App\Models\LeaveType::find($data['leave_type_id']);
        } elseif (!empty($data['type'])) {
            // Map frontend reden naar DB naam
            $map = [
                'verlof' => 'Vakantie',
                'overig' => 'Anders',
                'tvt' => 'TVT',
            ];
            $lookup = $map[strtolower($data['type'])] ?? ucfirst($data['type']);
            $leaveType = \App\Models\LeaveType::where('name', $lookup)->first();
            if ($leaveType) {
                $data['leave_type_id'] = $leaveType->id;
            }
        }

        if (!$leaveType) {
            return response()->json(['message' => 'Ongeldig verloftype.'], 422);
        }

        // Accepteer 'start_date'/'end_date' of 'from'/'to' van frontend
        $startInput = $data['start_date'] ?? $data['from'] ?? null;
        $endInput = $data['end_date'] ?? $data['to'] ?? null;

        if (!$startInput || !$endInput) {
            return response()->json(['message' => 'Start- en einddatum zijn vereist.'], 422);
        }

        $start = Carbon::parse($startInput)->startOfDay();
        $end = Carbon::parse($endInput)->endOfDay();

        if ($end->lt($start)) {
            return response()->json(['message' => 'Einddatum mag niet vóór startdatum zijn.'], 422);
        }

        // Bedrijfsregels
        $today = Carbon::today();
        if (strtolower($leaveType->name) === 'vakantie') {
            $daysUntil = $today->diffInDays($start, false);
            if ($daysUntil < 7) {
                return response()->json(['message' => 'Vakantie moet minimaal 7 dagen van tevoren worden aangevraagd.'], 422);
            }
        }

        // Bewijs verplichting
        if ($leaveType->requires_proof ?? false) {
            if (!$request->hasFile('proof')) {
                return response()->json(['message' => 'Bewijs is verplicht voor dit verloftype.'], 422);
            }
        }

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('proofs', 'public');
        }

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $user->id,
            'leave_type_id' => $data['leave_type_id'],
            'reason' => $data['reason'] ?? null,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'proof' => $proofPath,
            'status' => 'ingediend',
            'submitted_at' => now(),
            'notification_sent' => false,
        ]);

        Log::info('Verlofaanvraag ingediend', ['user_id' => $user->id, 'leave_request_id' => $leaveRequest->id]);

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
