<?php

namespace App\Http\Controllers;

use App\Mail\LeaveRequestStatusChanged;
use App\Models\LeaveRequest;
use App\Services\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class LeaveApprovalController extends Controller
{
    protected $leaveBalanceService;

    public function __construct(LeaveBalanceService $leaveBalanceService)
    {
        $this->leaveBalanceService = $leaveBalanceService;
        $this->middleware('auth');
    }

    /**
     * Display a listing of leave requests for approval
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::where('status', 'pending')
            ->orderBy('created_at', 'desc');

        // Filter by employee if specified
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $leaveRequests = $query->paginate(15);

        // Calculate KPIs
        $kpiOpen = LeaveRequest::where('status', 'pending')->count();
        $kpiReviewedToday = LeaveRequest::whereIn('status', ['approved', 'rejected'])
            ->whereDate('updated_at', today())
            ->count();
        $kpiMonthTotal = LeaveRequest::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('Requests.manager-requestsboard', [
            'requests' => $leaveRequests,
            'isDeletedView' => false,
            'kpiOpen' => $kpiOpen,
            'kpiReviewedToday' => $kpiReviewedToday,
            'kpiMonthTotal' => $kpiMonthTotal,
        ]);
    }

    /**
     * Approve a leave request
     */
    public function approve(LeaveRequest $leaveRequest, Request $request)
    {
        // Check authorization
        if (!auth()->user()->can('approve', $leaveRequest)) {
            return back()->with('error', 'Je hebt geen rechten om dit verzoek goed te keuren.');
        }

        // Calculate duration_hours if not set
        if (!$leaveRequest->duration_hours) {
            $leaveRequest->duration_hours = $this->leaveBalanceService->calculateDurationHours($leaveRequest);
        }

        // Approve the request
        $leaveRequest->status = 'approved';
        $leaveRequest->approved_at = now();
        $leaveRequest->approved_by = auth()->id();
        $leaveRequest->save();

        // Send notification email
        try {
            Mail::to($leaveRequest->employee->email)->send(
                new LeaveRequestStatusChanged($leaveRequest, 'approved')
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send approval email: ' . $e->getMessage());
        }

        return back()->with('success', 'Verlofverzoek goedgekeurd.');
    }

    /**
     * Reject a leave request
     */
    public function reject(LeaveRequest $leaveRequest, Request $request)
    {
        // Check authorization
        if (!auth()->user()->can('reject', $leaveRequest)) {
            return back()->with('error', 'Je hebt geen rechten om dit verzoek af te wijzen.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:5|max:255',
        ]);

        // Reject the request
        $leaveRequest->status = 'rejected';
        $leaveRequest->save();

        // Send notification email
        try {
            Mail::to($leaveRequest->employee->email)->send(
                new LeaveRequestStatusChanged($leaveRequest, 'rejected')
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send rejection email: ' . $e->getMessage());
        }

        return back()->with('success', 'Verlofverzoek afgewezen.');
    }

    /**
     * Hide a leave request (soft delete)
     */
    public function hide(LeaveRequest $leaveRequest)
    {
        // Check authorization
        if (auth()->id() !== $leaveRequest->employee_id && !auth()->user()->can('hide', $leaveRequest)) {
            return back()->with('error', 'Je hebt geen rechten om dit verzoek te verbergen.');
        }

        $leaveRequest->delete();

        return back()->with('success', 'Verlofverzoek verborgen.');
    }

    /**
     * Display deleted leave requests
     */
    public function deleted(Request $request)
    {
        $leaveRequests = LeaveRequest::onlyTrashed()
            ->where('employee_id', auth()->id())
            ->orderBy('deleted_at', 'desc')
            ->paginate(15);

        return view('Requests.manager-requestsboard', [
            'requests' => $leaveRequests,
            'isDeletedView' => true,
            'kpiOpen' => 0,
            'kpiReviewedToday' => 0,
            'kpiMonthTotal' => 0,
        ]);
    }

    /**
     * Restore a soft deleted leave request
     */
    public function restore($id)
    {
        $leaveRequest = LeaveRequest::withTrashed()->findOrFail($id);

        // Check authorization
        if (auth()->id() !== $leaveRequest->employee_id) {
            return back()->with('error', 'Je hebt geen rechten om dit verzoek te herstellen.');
        }

        $leaveRequest->restore();

        return back()->with('success', 'Verlofverzoek hersteld.');
    }
}
