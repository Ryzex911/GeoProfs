<?php
namespace App\Http\Controllers;

use App\Http\Controllers\LeaveController;
use App\Models\LeaveRequest;

class LeaveApprovalController extends Controller
{
public function index()
{
$requests = LeaveRequest::pending()
->with(['employee', 'leaveType'])
->latest()
->get();

return view('admin.leave-requests.index', [
'requests' => $requests,
]);
}

public function approve(LeaveRequest $leaveRequest)
{
$leaveRequest->update([
'status' => LeaveRequest::STATUS_APPROVED,
'approved_by' => auth()->id(), // mag null zijn
'approved_at' => now(),
]);

return back();
}

public function reject(LeaveRequest $leaveRequest)
{
$leaveRequest->update([
'status' => LeaveRequest::STATUS_REJECTED,
'approved_by' => auth()->id(),
'approved_at' => now(),
]);

return back();
}
}
