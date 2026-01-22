<?php

namespace App\Http\Controllers;

use App\Mail\LeaveRequestStatusChanged;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class LeaveApprovalController extends Controller
{
    public function index()
    {
        $this->authorize('leaveApprovePage', LeaveRequest::class);

        $requests = LeaveRequest::query()
            ->with(['employee', 'leaveType'])
            ->latest('submitted_at')
            ->get();

        [$kpiOpen, $kpiReviewedToday, $kpiMonthTotal] = $this->kpis();

        return view('Requests.manager-requestsboard', [
            'requests' => $requests,
            'kpiOpen' => $kpiOpen,
            'kpiReviewedToday' => $kpiReviewedToday,
            'kpiMonthTotal' => $kpiMonthTotal,
            'isDeletedView' => false,
        ]);
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        $this->authorize('approve', $leaveRequest);

        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            return back();
        }

        // Bereken duration_hours bij goedkeuring
        $leaveService = new LeaveService();
        $leaveService->calculateAndSaveDurationHours($leaveRequest);

        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'opmerking' => null,
        ]);

         Mail::to($leaveRequest->employee->email)->send(new LeaveRequestStatusChanged($leaveRequest));

        return back();
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorize('reject', $leaveRequest);

        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            return back();
        }

        $note = trim((string) $request->input('reason', ''));

        $leaveRequest->update([
            'status'      => LeaveRequest::STATUS_REJECTED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'opmerking'   => $note !== '' ? $note : null,
        ]);

        Mail::to($leaveRequest->employee->email)
            ->send(new LeaveRequestStatusChanged($leaveRequest));

        return back();
    }

    public function hide(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->trashed()) {
            return back();
        }

        $leaveRequest->delete();
        return back();
    }

    public function deleted()
    {
        $requests = LeaveRequest::onlyTrashed()
            ->with(['employee', 'leaveType'])
            ->latest('deleted_at')
            ->get();

        [$kpiOpen, $kpiReviewedToday, $kpiMonthTotal] = $this->kpis();

        return view('Requests.manager-requestsboard', [
            'requests' => $requests,
            'kpiOpen' => $kpiOpen,
            'kpiReviewedToday' => $kpiReviewedToday,
            'kpiMonthTotal' => $kpiMonthTotal,
            'isDeletedView' => true,
        ]);
    }

    public function restore($id)
    {
        $request = LeaveRequest::withTrashed()->findOrFail($id);

        if (!$request->trashed()) {
            return back();
        }

        $request->restore();
        return back();
    }

    private function kpis(): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now()->endOfMonth();

        $kpiOpen = LeaveRequest::where('status', LeaveRequest::STATUS_PENDING)->count();

        $kpiReviewedToday = LeaveRequest::whereIn('status', [
            LeaveRequest::STATUS_APPROVED,
            LeaveRequest::STATUS_REJECTED,
        ])->whereDate('approved_at', $today)->count();

        $kpiMonthTotal = LeaveRequest::whereBetween('submitted_at', [$startOfMonth, $endOfMonth])->count();

        return [$kpiOpen, $kpiReviewedToday, $kpiMonthTotal];
    }
}
