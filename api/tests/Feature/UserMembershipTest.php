<?php

namespace Tests\Feature;

use App\Models\MembershipPlan;
use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserMembershipTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->adminToken = $this->admin->createToken('admin-token')->plainTextToken;
    }

    /** @test */
    public function authenticated_user_can_list_memberships(): void
    {
        UserMembership::factory()->count(3)->create();

        $response = $this->withToken($this->adminToken)->getJson('/api/user-memberships');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function membership_list_includes_user_and_plan(): void
    {
        UserMembership::factory()->create();

        $response = $this->withToken($this->adminToken)->getJson('/api/user-memberships');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user',
                        'plan',
                        'start_date',
                        'end_date',
                        'status',
                    ]
                ]
            ]);
    }

    /** @test */
    public function admin_can_create_membership_for_user(): void
    {
        $user = User::factory()->create();
        $plan = MembershipPlan::factory()->create([
            'duration_days' => 30,
            'session_count' => 10,
        ]);

        $response = $this->withToken($this->adminToken)->postJson('/api/user-memberships', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Üyelik başarıyla oluşturuldu.',
            ]);

        $this->assertDatabaseHas('user_memberships', [
            'user_id' => $user->id,
            'membership_plan_id' => $plan->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function membership_creation_calculates_dates_correctly(): void
    {
        $user = User::factory()->create();
        $plan = MembershipPlan::factory()->create([
            'duration_days' => 30,
        ]);

        $this->withToken($this->adminToken)->postJson('/api/user-memberships', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $membership = UserMembership::first();

        $this->assertNotNull($membership->start_date);
        $this->assertNotNull($membership->end_date);

        $expectedEndDate = $membership->start_date->copy()->addDays(30);
        $this->assertEquals($expectedEndDate->toDateString(), $membership->end_date->toDateString());
    }

    /** @test */
    public function membership_creation_sets_remaining_sessions(): void
    {
        $user = User::factory()->create();
        $plan = MembershipPlan::factory()->create([
            'session_count' => 10,
        ]);

        $this->withToken($this->adminToken)->postJson('/api/user-memberships', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $membership = UserMembership::first();

        $this->assertEquals(10, $membership->remaining_sessions);
    }

    /** @test */
    public function membership_creation_sets_remaining_days(): void
    {
        $user = User::factory()->create();
        $plan = MembershipPlan::factory()->create([
            'duration_days' => 30,
        ]);

        $this->withToken($this->adminToken)->postJson('/api/user-memberships', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $membership = UserMembership::first();

        $this->assertEquals(30, $membership->remaining_days);
        $this->assertEquals(30, $membership->total_days);
    }

    /** @test */
    public function membership_without_duration_has_no_end_date(): void
    {
        $user = User::factory()->create();
        $plan = MembershipPlan::factory()->create([
            'duration_days' => null,
        ]);

        $this->withToken($this->adminToken)->postJson('/api/user-memberships', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $membership = UserMembership::first();

        $this->assertNull($membership->end_date);
        $this->assertEquals(0, $membership->remaining_days);
    }

    /** @test */
    public function membership_creation_requires_user_id(): void
    {
        $plan = MembershipPlan::factory()->create();

        $response = $this->withToken($this->adminToken)->postJson('/api/user-memberships', [
            'plan_id' => $plan->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    /** @test */
    public function membership_creation_requires_plan_id(): void
    {
        $user = User::factory()->create();

        $response = $this->withToken($this->adminToken)->postJson('/api/user-memberships', [
            'user_id' => $user->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plan_id']);
    }

    /** @test */
    public function user_id_must_exist(): void
    {
        $plan = MembershipPlan::factory()->create();

        $response = $this->withToken($this->adminToken)->postJson('/api/user-memberships', [
            'user_id' => 999,
            'plan_id' => $plan->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    /** @test */
    public function plan_id_must_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->withToken($this->adminToken)->postJson('/api/user-memberships', [
            'user_id' => $user->id,
            'plan_id' => 999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plan_id']);
    }

    /** @test */
    public function admin_can_update_membership_status(): void
    {
        $membership = UserMembership::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->withToken($this->adminToken)->putJson("/api/user-memberships/{$membership->id}", [
            'status' => 'expired',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Durum güncellendi.',
            ]);

        $membership->refresh();
        $this->assertEquals('expired', $membership->status);
    }

    /** @test */
    public function status_must_be_valid(): void
    {
        $membership = UserMembership::factory()->create();

        $response = $this->withToken($this->adminToken)->putJson("/api/user-memberships/{$membership->id}", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function all_valid_statuses_are_accepted(): void
    {
        $validStatuses = ['active', 'expired', 'cancelled'];

        foreach ($validStatuses as $status) {
            $membership = UserMembership::factory()->create(['status' => 'active']);

            $this->withToken($this->adminToken)->putJson("/api/user-memberships/{$membership->id}", [
                'status' => $status,
            ])->assertStatus(200);

            $membership->delete();
        }
    }

    /** @test */
    public function admin_can_delete_membership(): void
    {
        $membership = UserMembership::factory()->create();

        $response = $this->withToken($this->adminToken)->deleteJson("/api/user-memberships/{$membership->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Üyelik silindi.'
            ]);

        $this->assertDatabaseMissing('user_memberships', [
            'id' => $membership->id,
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_list_memberships(): void
    {
        UserMembership::factory()->count(3)->create();

        $response = $this->getJson('/api/user-memberships');

        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_membership(): void
    {
        $user = User::factory()->create();
        $plan = MembershipPlan::factory()->create();

        $response = $this->postJson('/api/user-memberships', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_membership(): void
    {
        $membership = UserMembership::factory()->create();

        $response = $this->putJson("/api/user-memberships/{$membership->id}", [
            'status' => 'cancelled',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_user_cannot_delete_membership(): void
    {
        $membership = UserMembership::factory()->create();

        $response = $this->deleteJson("/api/user-memberships/{$membership->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function memberships_are_ordered_by_created_at_desc(): void
    {
        $oldMembership = UserMembership::factory()->create(['created_at' => now()->subDays(3)]);
        $newMembership = UserMembership::factory()->create(['created_at' => now()]);

        $response = $this->withToken($this->adminToken)->getJson('/api/user-memberships');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals($newMembership->id, $data[0]['id']);
        $this->assertEquals($oldMembership->id, $data[1]['id']);
    }

    /** @test */
    public function membership_status_defaults_to_active(): void
    {
        $user = User::factory()->create();
        $plan = MembershipPlan::factory()->create();

        $this->withToken($this->adminToken)->postJson('/api/user-memberships', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $membership = UserMembership::first();
        $this->assertEquals('active', $membership->status);
    }

    /** @test */
    public function last_check_in_is_set_on_creation(): void
    {
        $user = User::factory()->create();
        $plan = MembershipPlan::factory()->create();

        $this->withToken($this->adminToken)->postJson('/api/user-memberships', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $membership = UserMembership::first();
        $this->assertNotNull($membership->last_check_in);
    }

    /** @test */
    public function user_can_have_multiple_memberships_over_time(): void
    {
        $user = User::factory()->create();
        $plan = MembershipPlan::factory()->create(['duration_days' => 30]);

        // First membership
        $this->withToken($this->adminToken)->postJson('/api/user-memberships', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        // Expire first membership
        $membership1 = UserMembership::first();
        $this->withToken($this->adminToken)->putJson("/api/user-memberships/{$membership1->id}", [
            'status' => 'expired',
        ]);

        // Second membership
        $this->withToken($this->adminToken)->postJson('/api/user-memberships', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $this->assertDatabaseCount('user_memberships', 2);
    }

    /** @test */
    public function plan_with_zero_sessions_creates_membership_with_zero_sessions(): void
    {
        $user = User::factory()->create();
        $plan = MembershipPlan::factory()->create([
            'session_count' => 0,
        ]);

        $this->withToken($this->adminToken)->postJson('/api/user-memberships', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $membership = UserMembership::first();
        $this->assertEquals(0, $membership->remaining_sessions);
    }
}
