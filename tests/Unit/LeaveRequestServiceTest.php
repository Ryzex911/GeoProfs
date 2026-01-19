<?php

namespace Tests\Unit;

use App\Models\LeaveRequest;
use App\Repositories\LeaveRequestRepositoryInterface;
use App\Services\LeaveRequestService;
use App\Services\NotificationService;
use Exception;
use Tests\TestCase;

class LeaveRequestServiceTest extends TestCase
{
    private LeaveRequestService $service;
    private $leaveRequestRepository;
    private $notificationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->leaveRequestRepository = \Mockery::mock(LeaveRequestRepositoryInterface::class);
        $this->notificationService = \Mockery::mock(NotificationService::class);
        $this->service = new LeaveRequestService($this->leaveRequestRepository, $this->notificationService);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    // 1. Happy path: normale goedkeuring
    public function test_approveRequest_whenPending_setsStatusApproved_andSendsNotification()
    {
        // Arrange
        $leaveRequest = \Mockery::mock(LeaveRequest::class)->makePartial();
        $leaveRequest->status = LeaveRequest::STATUS_PENDING;
        $leaveRequest->employee_id = 2;
        $managerId = 1;

        $this->leaveRequestRepository->shouldReceive('findById')->with(1)->andReturn($leaveRequest);
        $this->leaveRequestRepository->shouldReceive('save')->with($leaveRequest)->once();
        $this->notificationService->shouldReceive('sendApproval')->with($leaveRequest)->once();

        // Act
        $this->service->approveRequest(1, $managerId);

        // Assert
        $this->assertEquals(LeaveRequest::STATUS_APPROVED, $leaveRequest->status);
        $this->assertEquals($managerId, $leaveRequest->approved_by);
        $this->assertNotNull($leaveRequest->approved_at);
        $this->assertNull($leaveRequest->opmerking);
    }

    // 2. Notificaties / side-effects (verifieer aanroepen van NotificationService)
    public function test_approveRequest_notificationIsSent()
    {
        // Arrange
        $leaveRequest = \Mockery::mock(LeaveRequest::class)->makePartial();
        $leaveRequest->status = LeaveRequest::STATUS_PENDING;
        $leaveRequest->employee_id = 2;
        $managerId = 1;

        $this->leaveRequestRepository->shouldReceive('findById')->with(1)->andReturn($leaveRequest);
        $this->leaveRequestRepository->shouldReceive('save')->with($leaveRequest)->once();
        $this->notificationService->shouldReceive('sendApproval')->with($leaveRequest)->once();

        // Act
        $this->service->approveRequest(1, $managerId);

        // Assert - notification is verified above
        $this->assertTrue(true);
    }

    // 3. Niet-gevonden request (exception)
    public function test_approveRequest_whenRequestNotFound_throwsException()
    {
        // Arrange
        $managerId = 1;

        $this->leaveRequestRepository->shouldReceive('findById')->with(999)->andReturn(null);

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Request not found');

        $this->service->approveRequest(999, $managerId);
    }

    // 4. Verkeerde status / idempotentie (al goedgekeurd)
    public function test_approveRequest_whenNotPending_throwsException()
    {
        // Arrange
        $leaveRequest = \Mockery::mock(LeaveRequest::class)->makePartial();
        $leaveRequest->status = LeaveRequest::STATUS_APPROVED;
        $managerId = 1;

        $this->leaveRequestRepository->shouldReceive('findById')->with(1)->andReturn($leaveRequest);

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Request is not pending');

        $this->service->approveRequest(1, $managerId);
    }

    // 5. Optioneel: al afgekeurd (randgeval)
    public function test_approveRequest_whenAlreadyRejected_throwsException()
    {
        // Arrange
        $leaveRequest = \Mockery::mock(LeaveRequest::class)->makePartial();
        $leaveRequest->status = LeaveRequest::STATUS_REJECTED;
        $managerId = 1;

        $this->leaveRequestRepository->shouldReceive('findById')->with(1)->andReturn($leaveRequest);

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Request is not pending');

        $this->service->approveRequest(1, $managerId);
    }

    // 6. Optioneel: repository save wordt aangeroepen (randgeval)
    public function test_approveRequest_repositorySaveIsCalled()
    {
        // Arrange
        $leaveRequest = \Mockery::mock(LeaveRequest::class)->makePartial();
        $leaveRequest->status = LeaveRequest::STATUS_PENDING;
        $leaveRequest->employee_id = 2;
        $managerId = 1;

        $this->leaveRequestRepository->shouldReceive('findById')->with(1)->andReturn($leaveRequest);
        $this->leaveRequestRepository->shouldReceive('save')->with($leaveRequest)->once();
        $this->notificationService->shouldReceive('sendApproval')->with($leaveRequest)->once();

        // Act
        $this->service->approveRequest(1, $managerId);

        // Assert - save is verified above
        $this->assertTrue(true);
    }
}
