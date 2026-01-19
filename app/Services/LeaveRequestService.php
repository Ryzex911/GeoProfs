<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Repositories\LeaveRequestRepositoryInterface;
use Exception;

class LeaveRequestService
{
    private LeaveRequestRepositoryInterface $leaveRequestRepository;
    private NotificationService $notificationService;

    public function __construct(LeaveRequestRepositoryInterface $leaveRequestRepository, NotificationService $notificationService)
    {
        $this->leaveRequestRepository = $leaveRequestRepository;
        $this->notificationService = $notificationService;
    }

    public function approveRequest(int $requestId, int $managerId): void
    {
        $leaveRequest = $this->leaveRequestRepository->findById($requestId);

        if (!$leaveRequest) {
            throw new Exception('Request not found');
        }

        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            throw new Exception('Request is not pending');
        }

        $leaveRequest->status = LeaveRequest::STATUS_APPROVED;
        $leaveRequest->approved_by = $managerId;
        $leaveRequest->approved_at = now();
        $leaveRequest->opmerking = null;

        $this->leaveRequestRepository->save($leaveRequest);

        $this->notificationService->sendApproval($leaveRequest);
    }

    public function rejectRequest(int $requestId, int $managerId, ?string $reason = null): void
    {
        $leaveRequest = $this->leaveRequestRepository->findById($requestId);

        if (!$leaveRequest) {
            throw new Exception('Request not found');
        }

        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            throw new Exception('Request is not pending');
        }

        $leaveRequest->status = LeaveRequest::STATUS_REJECTED;
        $leaveRequest->approved_by = $managerId;
        $leaveRequest->approved_at = now();
        $leaveRequest->opmerking = $reason;

        $this->leaveRequestRepository->save($leaveRequest);

        $this->notificationService->sendRejection($leaveRequest);
    }
}
