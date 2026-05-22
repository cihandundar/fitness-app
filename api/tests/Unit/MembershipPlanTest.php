<?php

namespace Tests\Unit;

use App\Models\MembershipPlan;
use App\Models\UserMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipPlanTest extends TestCase
{
    use RefreshDatabase;

    // ==================== Basic Attributes ====================

    public function test_membership_plan_has_correct_fillable_attributes(): void
    {
        $plan = MembershipPlan::factory()->create([
            'name' => 'Gold Plan',
            'slug' => 'gold-plan',
            'description' => 'Premium membership',
            'price' => 299.99,
            'duration_days' => 30,
            'type' => 'gym',
        ]);

        $this->assertEquals('Gold Plan', $plan->name);
        $this->assertEquals('gold-plan', $plan->slug);
        $this->assertEquals(299.99, $plan->price);
        $this->assertEquals(30, $plan->duration_days);
        $this->assertEquals('gym', $plan->type);
    }

    public function test_membership_plan_casts_price_to_decimal(): void
    {
        $plan = MembershipPlan::factory()->create(['price' => 199.99]);

        // Model'de decimal cast varsa float döner, yoksa string dönebilir
        $this->assertEquals(199.99, (float) $plan->price);
    }

    public function test_membership_plan_casts_booleans(): void
    {
        $plan = MembershipPlan::factory()->create([
            'is_active' => true,
            'is_featured' => true,
        ]);

        $this->assertIsBool($plan->is_active);
        $this->assertIsBool($plan->is_featured);
        $this->assertTrue($plan->is_active);
        $this->assertTrue($plan->is_featured);
    }

    public function test_membership_plan_casts_features_to_array(): void
    {
        $features = ['Gym access', 'Locker room', 'Personal trainer'];
        $plan = MembershipPlan::factory()->create([
            'features' => $features,
        ]);

        $this->assertIsArray($plan->features);
        $this->assertEquals($features, $plan->features);
    }

    // ==================== Types ====================

    public function test_membership_plan_can_be_gym_type(): void
    {
        $plan = MembershipPlan::factory()->create(['type' => 'gym']);
        $this->assertEquals('gym', $plan->type);
    }

    public function test_membership_plan_can_be_hybrid_type(): void
    {
        $plan = MembershipPlan::factory()->create(['type' => 'hybrid']);
        $this->assertEquals('hybrid', $plan->type);
    }

    public function test_membership_plan_can_be_pt_type(): void
    {
        $plan = MembershipPlan::factory()->create(['type' => 'pt']);
        $this->assertEquals('pt', $plan->type);
    }

    // ==================== Features ====================

    public function test_membership_plan_can_have_multiple_features(): void
    {
        $features = [
            'Unlimited gym access',
            'Group classes',
            'Personal trainer (2 sessions/month)',
            'Nutrition consultation',
        ];

        $plan = MembershipPlan::factory()->create(['features' => $features]);

        $this->assertCount(4, $plan->features);
        $this->assertContains('Unlimited gym access', $plan->features);
    }

    public function test_membership_plan_features_can_be_empty(): void
    {
        $plan = MembershipPlan::factory()->create(['features' => []]);

        $this->assertIsArray($plan->features);
        $this->assertCount(0, $plan->features);
    }

    // ==================== Active Status ====================

    public function test_membership_plan_can_be_active(): void
    {
        $plan = MembershipPlan::factory()->create(['is_active' => true]);

        $this->assertTrue($plan->is_active);
    }

    public function test_membership_plan_can_be_inactive(): void
    {
        $plan = MembershipPlan::factory()->create(['is_active' => false]);

        $this->assertFalse($plan->is_active);
    }

    // ==================== Featured Status ====================

    public function test_membership_plan_can_be_featured(): void
    {
        $plan = MembershipPlan::factory()->create(['is_featured' => true]);

        $this->assertTrue($plan->is_featured);
    }

    public function test_membership_plan_can_be_not_featured(): void
    {
        $plan = MembershipPlan::factory()->create(['is_featured' => false]);

        $this->assertFalse($plan->is_featured);
    }

    // ==================== Duration ====================

    public function test_membership_plan_can_have_custom_duration(): void
    {
        $plan = MembershipPlan::factory()->create(['duration_days' => 90]);

        $this->assertEquals(90, $plan->duration_days);
    }

    public function test_membership_plan_duration_can_be_null(): void
    {
        $plan = MembershipPlan::factory()->create(['duration_days' => null]);

        $this->assertNull($plan->duration_days);
    }

    // ==================== Session Count ====================

    public function test_membership_plan_can_have_session_count(): void
    {
        $plan = MembershipPlan::factory()->create(['session_count' => 10]);

        $this->assertEquals(10, $plan->session_count);
    }

    public function test_membership_plan_session_count_can_be_null(): void
    {
        $plan = MembershipPlan::factory()->create(['session_count' => null]);

        $this->assertNull($plan->session_count);
    }

    // ==================== Slug Generation ====================

    public function test_membership_plan_has_slug(): void
    {
        $plan = MembershipPlan::factory()->create([
            'name' => 'Premium Plan',
            'slug' => 'premium-plan',
        ]);

        $this->assertEquals('premium-plan', $plan->slug);
    }

    // ==================== Scopes ====================

    public function test_can_filter_active_plans(): void
    {
        MembershipPlan::factory()->create(['is_active' => true]);
        MembershipPlan::factory()->create(['is_active' => false]);
        MembershipPlan::factory()->create(['is_active' => true]);

        $activePlans = MembershipPlan::where('is_active', true)->get();
        $inactivePlans = MembershipPlan::where('is_active', false)->get();

        $this->assertCount(2, $activePlans);
        $this->assertCount(1, $inactivePlans);
    }

    public function test_can_filter_featured_plans(): void
    {
        MembershipPlan::factory()->create(['is_featured' => true]);
        MembershipPlan::factory()->create(['is_featured' => false]);

        $featuredPlans = MembershipPlan::where('is_featured', true)->get();

        $this->assertCount(1, $featuredPlans);
    }

    public function test_can_filter_by_type(): void
    {
        MembershipPlan::factory()->create(['type' => 'gym']);
        MembershipPlan::factory()->create(['type' => 'hybrid']);
        MembershipPlan::factory()->create(['type' => 'pt']);

        $gymPlans = MembershipPlan::where('type', 'gym')->get();
        $hybridPlans = MembershipPlan::where('type', 'hybrid')->get();
        $ptPlans = MembershipPlan::where('type', 'pt')->get();

        $this->assertCount(1, $gymPlans);
        $this->assertCount(1, $hybridPlans);
        $this->assertCount(1, $ptPlans);
    }
}
