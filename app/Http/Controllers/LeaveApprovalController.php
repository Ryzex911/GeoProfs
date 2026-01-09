<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Carbon\Carbon;

class LeaveApprovalController extends Controller
{
    public function index()
    {
        $requests = LeaveRequest::with(['employee', 'leaveType'])
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
        // ✅ Alleen pending aanvragen mogen beoordeeld worden
        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            return back();
        }

        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back();
    }

    public function reject(LeaveRequest $leaveRequest)
    {
        // ✅ Alleen pending aanvragen mogen beoordeeld worden
        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            return back();
        }

        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_REJECTED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back();
    }

    public function hide(LeaveRequest $leaveRequest)
    {
        // ✅ Niet nog een keer deleten als hij al deleted is
        if ($leaveRequest->trashed()) {
            return back();
        }

        $leaveRequest->delete(); // Soft delete (deleted_at)
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

        // ✅ Alleen restoren als hij echt verwijderd is
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
        ])
            ->whereDate('approved_at', $today)
            ->count();

        $kpiMonthTotal = LeaveRequest::whereBetween('submitted_at', [$startOfMonth, $endOfMonth])->count();

        return [$kpiOpen, $kpiReviewedToday, $kpiMonthTotal];
    }
}
