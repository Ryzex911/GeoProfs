<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequestRequest;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Services\AuditLogger;

class LeaveController extends Controller
{
    // Overzicht
    public function index()
    {
        $user = Auth::user();

        $Requests = LeaveRequest::where('employee_id', $user->id)->paginate(15);

        return view('leave.index', compact('Requests'));
    }

    // Toon dashboard view (blade)
    public function dashboard()
    {
        $leaveTypes = \App\Models\LeaveType::all();
        return view('Requests.request-dashboard', compact('leaveTypes'));
    }

    public function store(StoreLeaveRequestRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        $start = Carbon::parse($data['start_date']);
        $end   = Carbon::parse($data['end_date']);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('proofs', 'public');
        }

        $proofLink = $data['proof_link'] ?? null;

        // ✅ Extra bescherming tegen dubbele submits (zelfde periode binnen 60 sec)
        $duplicate = LeaveRequest::where('employee_id', $user->id)
            ->where('leave_type_id', $data['leave_type_id'])
            ->where('start_date', $start)
            ->where('end_date', $end)
            ->where('status', LeaveRequest::STATUS_PENDING)
            ->where('created_at', '>=', now()->subMinute())
            ->exists();

        if ($duplicate) {
            // Als het een AJAX call is:
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deze verlofaanvraag is zojuist al ingediend. Probeer niet dubbel te versturen.',
                ], 409);
            }

            return redirect()->back()->with('error', 'Deze verlofaanvraag is zojuist al ingediend. Probeer niet dubbel te versturen.');
        }

        // ✅ 1x create (niet dubbel!)
        $leaveRequest = LeaveRequest::create([
            'employee_id'        => Auth::id(),
            'leave_type_id'      => $data['leave_type_id'],
            'reason'             => $data['reason'] ?? null,
            'start_date'         => $start->toDateString(),
            'end_date'           => $end->toDateString(),
            'proof'              => $proofPath,
            'proof_link'         => $proofLink,
            'status'             => LeaveRequest::STATUS_PENDING,
            'submitted_at'       => now(),
            'notification_sent'  => false,
        ]);

        // Audit (zorg dat je dit maar op 1 plek doet; als je al 'leave_request.created' logt elders, haal daar weg)
        try {
            /** @var AuditLogger $audit */
            $audit = app(AuditLogger::class);

            $audit->log(
                action: 'leave_request.created',
                auditable: $leaveRequest,
                oldValues: null,
                newValues: [
                    'leave_type_id' => $leaveRequest->leave_type_id,
                    'start_date'    => $leaveRequest->start_date,
                    'end_date'      => $leaveRequest->end_date,
                    'reason'        => $leaveRequest->reason,
                    'proof_file'    => $leaveRequest->proof ? true : false,
                    'proof_link'    => $leaveRequest->proof_link,
                ],
                logType: 'audit',
                description: 'Leave request submitted'
            );
        } catch (\Throwable $e) {
            // audit mag nooit je flow breken
        }

        Log::info('Verlofaanvraag ingediend', ['user_id' => $user->id, 'leave_request_id' => $leaveRequest->id]);
        // ✅ NIET meer handmatig AuditLog::create hier.
        // Audit gebeurt via LeaveRequestObserver (leave_request.created)
        Log::info('Leave request created', ['user_id' => $user->id, 'leave_request_id' => $leaveRequest->id]);

        // Return JSON voor AJAX; fallback redirect voor normale form submit
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'leave_request' => $leaveRequest], 201);
        }

        return redirect()->route('leave-requests.index')->with('success', 'Verlofaanvraag ingediend.');
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

        // Audit + mail gebeurt in LeaveRequestObserver via status change

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

}
