<?php

namespace Tests\Feature;

use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipPlanTest extends TestCase
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
    public function authenticated_user_can_list_membership_plans(): void
    {
        MembershipPlan::factory()->count(3)->create();

        $response = $this->withToken($this->adminToken)->getJson('/api/membership-plans');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function membership_plans_include_all_required_fields(): void
    {
        MembershipPlan::factory()->create([
            'name' => 'Gold Plan',
            'price' => 299.99,
            'type' => 'gym',
        ]);

        $response = $this->withToken($this->adminToken)->getJson('/api/membership-plans');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'price',
                        'type',
                        'is_featured',
                        'is_active',
                    ]
                ]
            ]);
    }

    /** @test */
    public function admin_can_create_membership_plan(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/membership-plans', [
            'name' => 'Premium Plan',
            'description' => 'Full access to all facilities',
            'price' => 199.99,
            'duration_days' => 30,
            'session_count' => 10,
            'type' => 'gym',
            'is_featured' => true,
            'is_active' => true,
            'features' => ['Sauna', 'Personal Trainer', 'Group Classes'],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Plan başarıyla oluşturuldu.',
                'data' => [
                    'name' => 'Premium Plan',
                    'price' => 199.99,
                    'type' => 'gym',
                ]
            ]);

        $this->assertDatabaseHas('membership_plans', [
            'name' => 'Premium Plan',
            'price' => 199.99,
        ]);
    }

    /** @test */
    public function plan_creation_requires_name(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/membership-plans', [
            'price' => 99.99,
            'type' => 'gym',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function plan_creation_requires_price(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/membership-plans', [
            'name' => 'Basic Plan',
            'type' => 'gym',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function plan_creation_requires_type(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/membership-plans', [
            'name' => 'Basic Plan',
            'price' => 99.99,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function plan_type_must_be_valid(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/membership-plans', [
            'name' => 'Invalid Plan',
            'price' => 99.99,
            'type' => 'invalid_type',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function all_valid_plan_types_are_accepted(): void
    {
        $validTypes = ['gym', 'pt', 'hybrid'];

        foreach ($validTypes as $type) {
            $response = $this->withToken($this->adminToken)->postJson('/api/membership-plans', [
                'name' => "Test {$type} Plan",
                'price' => 99.99,
                'type' => $type,
            ]);

            $response->assertStatus(201);
        }

        $this->assertDatabaseCount('membership_plans', 3);
    }

    /** @test */
    public function price_must_be_numeric(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/membership-plans', [
            'name' => 'Test Plan',
            'price' => 'not_a_number',
            'type' => 'gym',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function price_must_be_minimum_zero(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/membership-plans', [
            'name' => 'Free Plan',
            'price' => -10,
            'type' => 'gym',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function duration_days_must_be_positive_when_provided(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/membership-plans', [
            'name' => 'Test Plan',
            'price' => 99.99,
            'type' => 'gym',
            'duration_days' => -5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['duration_days']);
    }

    /** @test */
    public function session_count_must_be_non_negative(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/membership-plans', [
            'name' => 'Test Plan',
            'price' => 99.99,
            'type' => 'gym',
            'session_count' => -1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['session_count']);
    }

    /** @test */
    public function admin_can_view_single_plan(): void
    {
        $plan = MembershipPlan::factory()->create([
            'name' => 'Gold Plan',
            'price' => 299.99,
        ]);

        $response = $this->withToken($this->adminToken)->getJson("/api/membership-plans/{$plan->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $plan->id,
                    'name' => 'Gold Plan',
                ]
            ]);
    }

    /** @test */
    public function admin_can_update_plan(): void
    {
        $plan = MembershipPlan::factory()->create([
            'name' => 'Basic Plan',
            'price' => 99.99,
        ]);

        $response = $this->withToken($this->adminToken)->putJson("/api/membership-plans/{$plan->id}", [
            'name' => 'Updated Plan',
            'price' => 149.99,
            'type' => 'gym',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Plan başarıyla güncellendi.',
                'data' => [
                    'name' => 'Updated Plan',
                ]
            ]);

        $this->assertDatabaseHas('membership_plans', [
            'id' => $plan->id,
            'name' => 'Updated Plan',
            'price' => 149.99,
        ]);
    }

    /** @test */
    public function admin_can_update_plan_features(): void
    {
        $plan = MembershipPlan::factory()->create([
            'features' => ['Feature 1'],
        ]);

        $newFeatures = ['Sauna', 'Steam Room', 'Personal Trainer'];

        $response = $this->withToken($this->adminToken)->putJson("/api/membership-plans/{$plan->id}", [
            'features' => $newFeatures,
        ]);

        $response->assertStatus(200);

        $plan->refresh();
        $this->assertEquals($newFeatures, $plan->features);
    }

    /** @test */
    public function admin_can_toggle_featured_status(): void
    {
        $plan = MembershipPlan::factory()->create([
            'is_featured' => false,
        ]);

        $response = $this->withToken($this->adminToken)->putJson("/api/membership-plans/{$plan->id}", [
            'is_featured' => true,
        ]);

        $response->assertStatus(200);

        $plan->refresh();
        $this->assertTrue($plan->is_featured);
    }

    /** @test */
    public function admin_can_delete_plan(): void
    {
        $plan = MembershipPlan::factory()->create();

        $response = $this->withToken($this->adminToken)->deleteJson("/api/membership-plans/{$plan->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Plan başarıyla silindi.'
            ]);

        $this->assertDatabaseMissing('membership_plans', [
            'id' => $plan->id,
        ]);
    }

    /** @test */
    public function unauthenticated_user_can_list_plans(): void
    {
        MembershipPlan::factory()->count(3)->create();

        $response = $this->getJson('/api/membership-plans');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function unauthenticated_user_cannot_create_plan(): void
    {
        $response = $this->postJson('/api/membership-plans', [
            'name' => 'Test Plan',
            'price' => 99.99,
            'type' => 'gym',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_plan(): void
    {
        $plan = MembershipPlan::factory()->create();

        $response = $this->putJson("/api/membership-plans/{$plan->id}", [
            'name' => 'Updated',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_user_cannot_delete_plan(): void
    {
        $plan = MembershipPlan::factory()->create();

        $response = $this->deleteJson("/api/membership-plans/{$plan->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function slug_is_automatically_generated(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/membership-plans', [
            'name' => 'Premium Gold Plan',
            'price' => 299.99,
            'type' => 'gym',
        ]);

        $response->assertStatus(201);

        $plan = MembershipPlan::first();
        $this->assertStringContainsString('premium-gold-plan', $plan->slug);
        $this->assertStringEndsWith($plan->slug, $plan->slug);
    }

    /** @test */
    public function features_are_stored_as_json(): void
    {
        $features = ['Sauna', 'Steam Room', 'WiFi', 'Lockers'];

        $this->withToken($this->adminToken)->postJson('/api/membership-plans', [
            'name' => 'Test Plan',
            'price' => 99.99,
            'type' => 'gym',
            'features' => $features,
        ]);

        $plan = MembershipPlan::first();
        $this->assertEquals($features, $plan->features);
    }

    /** @test */
    public function featured_plans_can_be_filtered(): void
    {
        MembershipPlan::factory()->create(['is_featured' => true]);
        MembershipPlan::factory()->create(['is_featured' => false]);
        MembershipPlan::factory()->create(['is_featured' => true]);

        $response = $this->withToken($this->adminToken)->getJson('/api/membership-plans');

        $response->assertStatus(200);

        $featured = collect($response->json('data'))->filter(fn ($plan) => $plan['is_featured'] === true);
        $this->assertCount(2, $featured);
    }

    /** @test */
    public function active_plans_are_shown_by_default(): void
    {
        MembershipPlan::factory()->create(['is_active' => true]);
        MembershipPlan::factory()->create(['is_active' => true]);
        MembershipPlan::factory()->create(['is_active' => false]);

        $response = $this->withToken($this->adminToken)->getJson('/api/membership-plans');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function price_can_be_zero_for_free_plans(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/membership-plans', [
            'name' => 'Free Trial',
            'price' => 0,
            'type' => 'gym',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('membership_plans', [
            'name' => 'Free Trial',
            'price' => 0,
        ]);
    }

    /** @test */
    public function regular_user_cannot_create_plan(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/membership-plans', [
            'name' => 'Test Plan',
            'price' => 99.99,
            'type' => 'gym',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_update_plan(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;
        $plan = MembershipPlan::factory()->create();

        $response = $this->withToken($token)->putJson("/api/membership-plans/{$plan->id}", [
            'name' => 'Updated',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_delete_plan(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;
        $plan = MembershipPlan::factory()->create();

        $response = $this->withToken($token)->deleteJson("/api/membership-plans/{$plan->id}");

        $response->assertStatus(403);
    }
}
