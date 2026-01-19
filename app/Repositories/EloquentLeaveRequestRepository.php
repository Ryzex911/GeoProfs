<?php

namespace App\Repositories;

use App\Models\LeaveRequest;

class EloquentLeaveRequestRepository implements LeaveRequestRepositoryInterface
{
    public function findById(int $id): ?LeaveRequest
    {
        return LeaveRequest::find($id);
    }

    public function save(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->save();
    }
}
