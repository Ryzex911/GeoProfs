<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveRequestsTest extends TestCase
{
    use RefreshDatabase;

    private function makeLeaveType(string $name = 'Vakantie'): LeaveType
    {
        return LeaveType::firstOrCreate(['name' => $name]);
    }


    /** @test */
    public function employee_can_create_leave_request_with_valid_dates()
    {
        $user = User::factory()->create();
        $type = $this->makeLeaveType('Vakantie');

        $payload = [
            'leave_type_id' => $type->id,
            'start_date'    => now()->addDays(8)->toDateString(),
            'end_date'      => now()->addDays(10)->toDateString(),
            'reason'        => 'Feature test aanvraag',
        ];

        $this->actingAs($user)
            ->postJson('/leave-requests', $payload)
            ->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('leave_requests', [
            'employee_id'   => $user->id,
            'leave_type_id' => $type->id,
            'status'        => LeaveRequest::STATUS_PENDING,
        ]);
    }

    /** @test */
    public function employee_cannot_create_leave_request_if_start_date_is_less_than_7_days_ahead()
    {
        $user = User::factory()->create();
        $type = $this->makeLeaveType('Vakantie');

        $payload = [
            'leave_type_id' => $type->id,
            'start_date'    => now()->addDays(2)->toDateString(), // te vroeg
            'end_date'      => now()->addDays(3)->toDateString(),
            'reason'        => 'Te vroeg',
        ];

        // StoreLeaveRequestRequest hoort dit te blokkeren (422)
        $this->actingAs($user)
            ->postJson('/leave-requests', $payload)
            ->assertStatus(422);

        $this->assertDatabaseCount('leave_requests', 0);
    }

    /** @test */
    public function employee_can_cancel_own_pending_request()
    {
        $user = User::factory()->create();
        $type = $this->makeLeaveType('Vakantie');

        $req = LeaveRequest::create([
            'employee_id'   => $user->id,
            'leave_type_id' => $type->id,
            'reason'        => 'Cancel test',
            'start_date'    => now()->addDays(8)->toDateString(),
            'end_date'      => now()->addDays(9)->toDateString(),
            'status'        => LeaveRequest::STATUS_PENDING,
            'submitted_at'  => now(),
            'notification_sent' => false,
        ]);

        $this->actingAs($user)
            ->patch("/leave-requests/{$req->id}/cancel")
            ->assertStatus(302); // redirect back

        $this->assertDatabaseHas('leave_requests', [
            'id'     => $req->id,
            'status' => LeaveRequest::STATUS_CANCELED,
        ]);
    }

    /** @test */
    public function employee_can_delete_only_canceled_request()
    {
        $user = User::factory()->create();
        $type = $this->makeLeaveType('Vakantie');

        $req = LeaveRequest::create([
            'employee_id'   => $user->id,
            'leave_type_id' => $type->id,
            'reason'        => 'Delete test',
            'start_date'    => now()->addDays(8)->toDateString(),
            'end_date'      => now()->addDays(9)->toDateString(),
            'status'        => LeaveRequest::STATUS_CANCELED,
            'submitted_at'  => now(),
            'canceled_at'   => now(),
            'notification_sent' => false,
        ]);

        $this->actingAs($user)
            ->delete("/leave-requests/{$req->id}")
            ->assertStatus(302);

        // Als je model SoftDeletes gebruikt:
        $this->assertSoftDeleted('leave_requests', ['id' => $req->id]);
    }
}
