<?php

namespace Tests\Unit;

use App\Models\LeaveRequest;
use App\Repositories\LeaveRequestRepositoryInterface;
use App\Services\LeaveRequestService;
use App\Services\NotificationService;
use Exception;
use Tests\TestCase;

class LeaveRequestRejectTest extends TestCase
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

    // TC-MA02: Aanvraag afkeuren (reden optioneel)

    // 1. Happy path: reject without reason
    public function test_rejectRequest_whenPending_setsStatusRejected_andSaves()
    {
        // Arrange
        $leaveRequest = \Mockery::mock(LeaveRequest::class)->makePartial();
        $leaveRequest->status = LeaveRequest::STATUS_PENDING;
        $leaveRequest->employee_id = 2;
        $managerId = 1;

        $this->leaveRequestRepository->shouldReceive('findById')->with(1)->andReturn($leaveRequest);
        $this->leaveRequestRepository->shouldReceive('save')->with($leaveRequest)->once();
        $this->notificationService->shouldReceive('sendRejection')->with($leaveRequest)->once();

        // Act
        $this->service->rejectRequest(1, $managerId);

        // Assert
        $this->assertEquals(LeaveRequest::STATUS_REJECTED, $leaveRequest->status);
        $this->assertEquals($managerId, $leaveRequest->approved_by);
        $this->assertNotNull($leaveRequest->approved_at);
        $this->assertNull($leaveRequest->opmerking);
    }

    // 2. Happy path: reject with reason
    public function test_rejectRequest_withReason_savesReason_andSaves()
    {
        // Arrange
        $leaveRequest = \Mockery::mock(LeaveRequest::class)->makePartial();
        $leaveRequest->status = LeaveRequest::STATUS_PENDING;
        $leaveRequest->employee_id = 2;
        $managerId = 1;
        $reason = 'Projectdrukte';

        $this->leaveRequestRepository->shouldReceive('findById')->with(1)->andReturn($leaveRequest);
        $this->leaveRequestRepository->shouldReceive('save')->with($leaveRequest)->once();
        $this->notificationService->shouldReceive('sendRejection')->with($leaveRequest)->once();

        // Act
        $this->service->rejectRequest(1, $managerId, $reason);

        // Assert
        $this->assertEquals(LeaveRequest::STATUS_REJECTED, $leaveRequest->status);
        $this->assertEquals($managerId, $leaveRequest->approved_by);
        $this->assertNotNull($leaveRequest->approved_at);
        $this->assertEquals($reason, $leaveRequest->opmerking);
    }

    // 3. Not found exception
    public function test_rejectRequest_whenRequestNotFound_throwsException()
    {
        // Arrange
        $managerId = 1;

        $this->leaveRequestRepository->shouldReceive('findById')->with(999)->andReturn(null);

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Request not found');

        $this->service->rejectRequest(999, $managerId);
    }

    // 4. Already rejected exception
    public function test_rejectRequest_whenNotPending_throwsException()
    {
        // Arrange
        $leaveRequest = \Mockery::mock(LeaveRequest::class)->makePartial();
        $leaveRequest->status = LeaveRequest::STATUS_REJECTED;
        $managerId = 1;

        $this->leaveRequestRepository->shouldReceive('findById')->with(1)->andReturn($leaveRequest);

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Request is not pending');

        $this->service->rejectRequest(1, $managerId);
    }

    // 5. Repository save is called
    public function test_rejectRequest_repositorySaveIsCalled()
    {
        // Arrange
        $leaveRequest = \Mockery::mock(LeaveRequest::class)->makePartial();
        $leaveRequest->status = LeaveRequest::STATUS_PENDING;
        $leaveRequest->employee_id = 2;
        $managerId = 1;
        $reason = 'Projectdrukte';

        $this->leaveRequestRepository->shouldReceive('findById')->with(1)->andReturn($leaveRequest);
        $this->leaveRequestRepository->shouldReceive('save')->with($leaveRequest)->once();
        $this->notificationService->shouldReceive('sendRejection')->with($leaveRequest)->once();

        // Act
        $this->service->rejectRequest(1, $managerId, $reason);

        // Assert - save is verified above
        $this->assertTrue(true);
    }

    // 6. Optional: reason validation too long (but since no validation in service, perhaps skip or assume it's handled elsewhere)
    // For now, skip this one as the service doesn't validate length
}
