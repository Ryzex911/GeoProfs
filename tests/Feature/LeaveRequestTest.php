<?php

namespace Tests\Feature;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class LeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_reason_is_required_when_type_is_anders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $anders = LeaveType::firstOrCreate([
            'name' => 'Anders',
        ]);

        $response = $this->post('/leave-requests', [
            'leave_type_id' => $anders->id,
            'reason' => null, // leeg
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addDays(9)->toDateString(),
        ]);


        $response->assertSessionHasErrors(['reason']);
        $this->assertDatabaseCount('leave_requests', 0);
    }
}
