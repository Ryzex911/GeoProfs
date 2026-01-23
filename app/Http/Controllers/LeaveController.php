<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
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
        // StoreLeaveRequest wordt gebruikt om bedrijfsregels te valideren
        $data = $request->validated();

        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('proofs', 'public');
        }

        $leaveRequest = LeaveRequest::create([
            'employee_id' => Auth::id(),
            'leave_type_id' => $data['leave_type_id'],
            'reason' => $data['reason'] ?? null,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'proof' => $proofPath,
            'status' => LeaveRequest::STATUS_PENDING,
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

        if ($leaveRequest->status !== LeaveRequest::STATUS_CANCELED) {
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
        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            return redirect()->back();
        }

        $leaveRequest->status = LeaveRequest::STATUS_CANCELED;
        $leaveRequest->canceled_at = now();

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
            ->where('status', LeaveRequest::STATUS_PENDING)
            ->count();

        return view('requests.dashboard', compact('lopendeAanvragen'));
    }

    // API endpoint voor verlofsaldo
    public function getBalance()
    {
        $user = Auth::user();
        $year = request()->query('year', date('Y'));
        $service = app(\App\Services\LeaveBalanceService::class);

        return response()->json([
            'year' => (int) $year,
            'startsaldo_days' => $service->getStartSaldoDays($user, (int) $year),
            'used_days' => $service->getUsedDays($user, (int) $year),
            'remaining_days' => $service->getRemainingDays($user, (int) $year),
        ]);
    }

}
