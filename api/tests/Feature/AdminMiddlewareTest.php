<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Program;
use App\Models\Exercise;
use App\Models\EquipmentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $superAdmin;
    private User $trainer;
    private User $regularUser;
    private string $adminToken;
    private string $superAdminToken;
    private string $trainerToken;
    private string $userToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->superAdmin = User::factory()->superAdmin()->create();
        $this->trainer = User::factory()->trainer()->create();
        $this->regularUser = User::factory()->create();

        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;
        $this->superAdminToken = $this->superAdmin->createToken('test-token')->plainTextToken;
        $this->trainerToken = $this->trainer->createToken('test-token')->plainTextToken;
        $this->userToken = $this->regularUser->createToken('test-token')->plainTextToken;
    }

    // ==================== Program Management Tests ====================

    public function test_admin_can_create_program(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/programs', [
            'title' => 'Test Program',
            'description' => 'Test Description',
            'level' => 'beginner',
            'duration_weeks' => 8,
        ]);

        $response->assertStatus(201);
    }

    public function test_super_admin_can_create_program(): void
    {
        $response = $this->withToken($this->superAdminToken)->postJson('/api/programs', [
            'title' => 'Test Program',
            'description' => 'Test Description',
            'level' => 'beginner',
            'duration_weeks' => 8,
        ]);

        $response->assertStatus(201);
    }

    public function test_trainer_cannot_create_program(): void
    {
        $response = $this->withToken($this->trainerToken)->postJson('/api/programs', [
            'title' => 'Test Program',
            'description' => 'Test Description',
            'level' => 'beginner',
            'duration_weeks' => 8,
        ]);

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_create_program(): void
    {
        $response = $this->withToken($this->userToken)->postJson('/api/programs', [
            'title' => 'Test Program',
            'description' => 'Test Description',
            'level' => 'beginner',
            'duration_weeks' => 8,
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_create_program(): void
    {
        $response = $this->postJson('/api/programs', [
            'title' => 'Test Program',
            'description' => 'Test Description',
            'level' => 'beginner',
            'duration_weeks' => 8,
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_can_update_program(): void
    {
        $program = Program::factory()->create();

        $response = $this->withToken($this->adminToken)->putJson("/api/programs/{$program->id}", [
            'title' => 'Updated Program',
        ]);

        $response->assertStatus(200);
    }

    public function test_trainer_cannot_update_program(): void
    {
        $program = Program::factory()->create();

        $response = $this->withToken($this->trainerToken)->putJson("/api/programs/{$program->id}", [
            'title' => 'Updated Program',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_program(): void
    {
        $program = Program::factory()->create();

        $response = $this->withToken($this->adminToken)->deleteJson("/api/programs/{$program->id}");

        $response->assertStatus(204);
    }

    public function test_trainer_cannot_delete_program(): void
    {
        $program = Program::factory()->create();

        $response = $this->withToken($this->trainerToken)->deleteJson("/api/programs/{$program->id}");

        $response->assertStatus(403);
    }

    // ==================== Exercise Management Tests ====================

    public function test_admin_can_create_exercise(): void
    {
        $muscleGroup = \App\Models\MuscleGroup::factory()->create();
        $equipmentType = \App\Models\EquipmentType::factory()->create();

        $response = $this->withToken($this->adminToken)->postJson('/api/exercises', [
            'name' => 'Test Exercise',
            'slug' => 'test-exercise-' . uniqid(),
            'muscle_group_id' => $muscleGroup->id,
            'equipment_type_id' => $equipmentType->id,
            'muscle_group' => 'chest',
            'equipment' => 'dumbbells',
            'difficulty' => 'beginner',
        ]);

        $response->assertStatus(201);
    }

    public function test_trainer_cannot_create_exercise(): void
    {
        $muscleGroup = \App\Models\MuscleGroup::factory()->create();
        $equipmentType = \App\Models\EquipmentType::factory()->create();

        $response = $this->withToken($this->trainerToken)->postJson('/api/exercises', [
            'name' => 'Test Exercise',
            'slug' => 'test-exercise-' . uniqid(),
            'muscle_group_id' => $muscleGroup->id,
            'equipment_type_id' => $equipmentType->id,
            'muscle_group' => 'chest',
            'equipment' => 'dumbbells',
            'difficulty' => 'beginner',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_exercise(): void
    {
        $muscleGroup = \App\Models\MuscleGroup::factory()->create();
        $equipmentType = \App\Models\EquipmentType::factory()->create();
        $exercise = Exercise::factory()->create([
            'muscle_group_id' => $muscleGroup->id,
            'equipment_type_id' => $equipmentType->id,
        ]);

        $response = $this->withToken($this->adminToken)->putJson("/api/exercises/{$exercise->id}", [
            'name' => 'Updated Exercise',
            'slug' => 'updated-exercise-' . uniqid(),
        ]);

        $response->assertStatus(200);
    }

    public function test_trainer_cannot_update_exercise(): void
    {
        $muscleGroup = \App\Models\MuscleGroup::factory()->create();
        $equipmentType = \App\Models\EquipmentType::factory()->create();
        $exercise = Exercise::factory()->create([
            'muscle_group_id' => $muscleGroup->id,
            'equipment_type_id' => $equipmentType->id,
        ]);

        $response = $this->withToken($this->trainerToken)->putJson("/api/exercises/{$exercise->id}", [
            'name' => 'Updated Exercise',
            'slug' => 'updated-exercise-' . uniqid(),
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_exercise(): void
    {
        $muscleGroup = \App\Models\MuscleGroup::factory()->create();
        $equipmentType = \App\Models\EquipmentType::factory()->create();
        $exercise = Exercise::factory()->create([
            'muscle_group_id' => $muscleGroup->id,
            'equipment_type_id' => $equipmentType->id,
        ]);

        $response = $this->withToken($this->adminToken)->deleteJson("/api/exercises/{$exercise->id}");

        $response->assertStatus(200);
    }

    public function test_trainer_cannot_delete_exercise(): void
    {
        $muscleGroup = \App\Models\MuscleGroup::factory()->create();
        $equipmentType = \App\Models\EquipmentType::factory()->create();
        $exercise = Exercise::factory()->create([
            'muscle_group_id' => $muscleGroup->id,
            'equipment_type_id' => $equipmentType->id,
        ]);

        $response = $this->withToken($this->trainerToken)->deleteJson("/api/exercises/{$exercise->id}");

        $response->assertStatus(403);
    }

    // ==================== Equipment Type Reorder Tests ====================

    public function test_admin_can_reorder_equipment_types(): void
    {
        EquipmentType::factory()->create(['id' => 1, 'sort_order' => 1]);
        EquipmentType::factory()->create(['id' => 2, 'sort_order' => 2]);

        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types/reorder', [
            'orders' => [
                ['id' => 1, 'sort_order' => 2],
                ['id' => 2, 'sort_order' => 1],
            ],
        ]);

        $response->assertStatus(200);
    }

    public function test_trainer_cannot_reorder_equipment_types(): void
    {
        EquipmentType::factory()->create(['id' => 1, 'sort_order' => 1]);
        EquipmentType::factory()->create(['id' => 2, 'sort_order' => 2]);

        $response = $this->withToken($this->trainerToken)->postJson('/api/equipment-types/reorder', [
            'orders' => [
                ['id' => 1, 'sort_order' => 2],
                ['id' => 2, 'sort_order' => 1],
            ],
        ]);

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_reorder_equipment_types(): void
    {
        EquipmentType::factory()->create(['id' => 1, 'sort_order' => 1]);
        EquipmentType::factory()->create(['id' => 2, 'sort_order' => 2]);

        $response = $this->withToken($this->userToken)->postJson('/api/equipment-types/reorder', [
            'orders' => [
                ['id' => 1, 'sort_order' => 2],
                ['id' => 2, 'sort_order' => 1],
            ],
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_reorder_equipment_types(): void
    {
        EquipmentType::factory()->create(['id' => 1, 'sort_order' => 1]);
        EquipmentType::factory()->create(['id' => 2, 'sort_order' => 2]);

        $response = $this->postJson('/api/equipment-types/reorder', [
            'orders' => [
                ['id' => 1, 'sort_order' => 2],
                ['id' => 2, 'sort_order' => 1],
            ],
        ]);

        $response->assertStatus(401);
    }

    // ==================== Muscle Group Reorder Tests ====================

    public function test_admin_can_reorder_muscle_groups(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups/reorder', [
            'orders' => [
                ['id' => 1, 'sort_order' => 1],
            ],
        ]);

        // 404 dönebilir çünkü henüz muscle group yok, önemli olan 403 dönmemesi
        $this->assertNotEquals(403, $response->status());
        $this->assertNotEquals(401, $response->status());
    }

    public function test_trainer_cannot_reorder_muscle_groups(): void
    {
        $response = $this->withToken($this->trainerToken)->postJson('/api/muscle-groups/reorder', [
            'orders' => [
                ['id' => 1, 'sort_order' => 1],
            ],
        ]);

        $response->assertStatus(403);
    }

    // ==================== Branch Management Tests ====================

    public function test_admin_can_create_branch(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/branches', [
            'name' => 'Test Branch',
            'address' => 'Test Address',
            'city' => 'Test City',
        ]);

        $response->assertStatus(201);
    }

    public function test_trainer_cannot_create_branch(): void
    {
        $response = $this->withToken($this->trainerToken)->postJson('/api/branches', [
            'name' => 'Test Branch',
            'address' => 'Test Address',
            'city' => 'Test City',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_branch_order(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/branches/update-order', [
            'orders' => [
                ['id' => 1, 'sort_order' => 1],
            ],
        ]);

        // 404 dönebilir, önemli olan 403/401 dönmemesi
        $this->assertNotEquals(403, $response->status());
        $this->assertNotEquals(401, $response->status());
    }

    public function test_trainer_cannot_update_branch_order(): void
    {
        $response = $this->withToken($this->trainerToken)->postJson('/api/branches/update-order', [
            'orders' => [
                ['id' => 1, 'sort_order' => 1],
            ],
        ]);

        $response->assertStatus(403);
    }

    // ==================== Membership Plan Management Tests ====================

    public function test_admin_can_create_membership_plan(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/membership-plans', [
            'name' => 'Test Plan',
            'price' => 100,
            'duration_days' => 30,
            'type' => 'gym',
            'features' => ['Gym access', 'Locker room'],
        ]);

        $response->assertStatus(201);
    }

    public function test_trainer_cannot_create_membership_plan(): void
    {
        $response = $this->withToken($this->trainerToken)->postJson('/api/membership-plans', [
            'name' => 'Test Plan',
            'price' => 100,
            'duration_days' => 30,
            'type' => 'gym',
            'features' => ['Gym access', 'Locker room'],
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_membership_plan(): void
    {
        $plan = \App\Models\MembershipPlan::factory()->create();

        $response = $this->withToken($this->adminToken)->deleteJson("/api/membership-plans/{$plan->id}");

        $response->assertStatus(200);
    }

    public function test_trainer_cannot_delete_membership_plan(): void
    {
        $plan = \App\Models\MembershipPlan::factory()->create();

        $response = $this->withToken($this->trainerToken)->deleteJson("/api/membership-plans/{$plan->id}");

        $response->assertStatus(403);
    }

    // ==================== All Workout History Test ====================

    public function test_admin_can_get_all_workout_history(): void
    {
        $response = $this->withToken($this->adminToken)->getJson('/api/all-workout-history');

        // Boş liste dönebilir
        $this->assertNotEquals(403, $response->status());
        $this->assertNotEquals(401, $response->status());
    }

    public function test_trainer_cannot_get_all_workout_history(): void
    {
        $response = $this->withToken($this->trainerToken)->getJson('/api/all-workout-history');

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_get_all_workout_history(): void
    {
        $response = $this->withToken($this->userToken)->getJson('/api/all-workout-history');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_get_all_workout_history(): void
    {
        $response = $this->getJson('/api/all-workout-history');

        $response->assertStatus(401);
    }
}
