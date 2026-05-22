<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\EquipmentType;
use App\Models\MuscleGroup;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReorderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $trainer;
    private User $regularUser;
    private string $adminToken;
    private string $trainerToken;
    private string $userToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->trainer = User::factory()->trainer()->create();
        $this->regularUser = User::factory()->create();

        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;
        $this->trainerToken = $this->trainer->createToken('test-token')->plainTextToken;
        $this->userToken = $this->regularUser->createToken('test-token')->plainTextToken;
    }

    // ==================== Equipment Type Reorder Tests ====================

    public function test_admin_can_reorder_equipment_types(): void
    {
        $eq1 = EquipmentType::factory()->create(['id' => 1, 'sort_order' => 1]);
        $eq2 = EquipmentType::factory()->create(['id' => 2, 'sort_order' => 2]);
        $eq3 = EquipmentType::factory()->create(['id' => 3, 'sort_order' => 3]);

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/equipment-types/reorder', [
                'orders' => [
                    ['id' => 1, 'sort_order' => 3],
                    ['id' => 2, 'sort_order' => 1],
                    ['id' => 3, 'sort_order' => 2],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sıralama güncellendi'
            ]);

        // Veritabanında güncelleme kontrolü
        $this->assertDatabaseHas('equipment_types', [
            'id' => 1,
            'sort_order' => 3,
        ]);
        $this->assertDatabaseHas('equipment_types', [
            'id' => 2,
            'sort_order' => 1,
        ]);
        $this->assertDatabaseHas('equipment_types', [
            'id' => 3,
            'sort_order' => 2,
        ]);
    }

    public function test_trainer_cannot_reorder_equipment_types(): void
    {
        $eq1 = EquipmentType::factory()->create(['id' => 1, 'sort_order' => 1]);

        $response = $this->withToken($this->trainerToken)
            ->postJson('/api/equipment-types/reorder', [
                'orders' => [
                    ['id' => 1, 'sort_order' => 5],
                ],
            ]);

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_reorder_equipment_types(): void
    {
        $eq1 = EquipmentType::factory()->create(['id' => 1, 'sort_order' => 1]);

        $response = $this->withToken($this->userToken)
            ->postJson('/api/equipment-types/reorder', [
                'orders' => [
                    ['id' => 1, 'sort_order' => 5],
                ],
            ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_reorder_equipment_types(): void
    {
        $eq1 = EquipmentType::factory()->create(['id' => 1, 'sort_order' => 1]);

        $response = $this->postJson('/api/equipment-types/reorder', [
            'orders' => [
                ['id' => 1, 'sort_order' => 5],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_equipment_reorder_requires_orders_array(): void
    {
        $response = $this->withToken($this->adminToken)
            ->postJson('/api/equipment-types/reorder', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders']);
    }

    public function test_equipment_reorder_orders_items_must_have_id(): void
    {
        $response = $this->withToken($this->adminToken)
            ->postJson('/api/equipment-types/reorder', [
                'orders' => [
                    ['sort_order' => 1],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.id']);
    }

    public function test_equipment_reorder_orders_items_must_have_sort_order(): void
    {
        $response = $this->withToken($this->adminToken)
            ->postJson('/api/equipment-types/reorder', [
                'orders' => [
                    ['id' => 1],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.sort_order']);
    }

    public function test_equipment_reorder_equipment_id_must_exist(): void
    {
        EquipmentType::factory()->create(['id' => 1, 'sort_order' => 1]);

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/equipment-types/reorder', [
                'orders' => [
                    ['id' => 999, 'sort_order' => 1],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.id']);
    }

    public function test_equipment_reorder_sort_order_must_be_integer(): void
    {
        EquipmentType::factory()->create(['id' => 1, 'sort_order' => 1]);

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/equipment-types/reorder', [
                'orders' => [
                    ['id' => 1, 'sort_order' => 'abc'],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.sort_order']);
    }

    // ==================== Muscle Group Reorder Tests ====================

    public function test_admin_can_reorder_muscle_groups(): void
    {
        $mg1 = MuscleGroup::factory()->create(['id' => 1, 'sort_order' => 1]);
        $mg2 = MuscleGroup::factory()->create(['id' => 2, 'sort_order' => 2]);

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/muscle-groups/reorder', [
                'orders' => [
                    ['id' => 1, 'sort_order' => 2],
                    ['id' => 2, 'sort_order' => 1],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sıralama güncellendi'
            ]);

        $this->assertDatabaseHas('muscle_groups', [
            'id' => 1,
            'sort_order' => 2,
        ]);
        $this->assertDatabaseHas('muscle_groups', [
            'id' => 2,
            'sort_order' => 1,
        ]);
    }

    public function test_trainer_cannot_reorder_muscle_groups(): void
    {
        MuscleGroup::factory()->create(['id' => 1, 'sort_order' => 1]);

        $response = $this->withToken($this->trainerToken)
            ->postJson('/api/muscle-groups/reorder', [
                'orders' => [
                    ['id' => 1, 'sort_order' => 5],
                ],
            ]);

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_reorder_muscle_groups(): void
    {
        MuscleGroup::factory()->create(['id' => 1, 'sort_order' => 1]);

        $response = $this->withToken($this->userToken)
            ->postJson('/api/muscle-groups/reorder', [
                'orders' => [
                    ['id' => 1, 'sort_order' => 5],
                ],
            ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_reorder_muscle_groups(): void
    {
        MuscleGroup::factory()->create(['id' => 1, 'sort_order' => 1]);

        $response = $this->postJson('/api/muscle-groups/reorder', [
            'orders' => [
                ['id' => 1, 'sort_order' => 5],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_muscle_group_reorder_requires_orders_array(): void
    {
        $response = $this->withToken($this->adminToken)
            ->postJson('/api/muscle-groups/reorder', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders']);
    }

    public function test_muscle_group_reorder_orders_items_must_have_id(): void
    {
        $response = $this->withToken($this->adminToken)
            ->postJson('/api/muscle-groups/reorder', [
                'orders' => [
                    ['sort_order' => 1],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.id']);
    }

    public function test_muscle_group_reorder_orders_items_must_have_sort_order(): void
    {
        $response = $this->withToken($this->adminToken)
            ->postJson('/api/muscle-groups/reorder', [
                'orders' => [
                    ['id' => 1],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.sort_order']);
    }

    public function test_muscle_group_reorder_id_must_exist(): void
    {
        MuscleGroup::factory()->create(['id' => 1, 'sort_order' => 1]);

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/muscle-groups/reorder', [
                'orders' => [
                    ['id' => 999, 'sort_order' => 1],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.id']);
    }

    public function test_muscle_group_reorder_sort_order_must_be_integer(): void
    {
        MuscleGroup::factory()->create(['id' => 1, 'sort_order' => 1]);

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/muscle-groups/reorder', [
                'orders' => [
                    ['id' => 1, 'sort_order' => 'xyz'],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.sort_order']);
    }

    // ==================== Branch Update Order Tests ====================

    public function test_admin_can_update_branch_order(): void
    {
        $branch1 = Branch::factory()->create(['id' => 1, 'order' => 1]);
        $branch2 = Branch::factory()->create(['id' => 2, 'order' => 2]);

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/branches/update-order', [
                'orders' => [
                    ['id' => 1, 'order' => 2],
                    ['id' => 2, 'order' => 1],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Sıralama güncellendi.'
            ]);

        $this->assertDatabaseHas('branches', [
            'id' => 1,
            'order' => 2,
        ]);
        $this->assertDatabaseHas('branches', [
            'id' => 2,
            'order' => 1,
        ]);
    }

    public function test_trainer_cannot_update_branch_order(): void
    {
        Branch::factory()->create(['id' => 1, 'order' => 1]);

        $response = $this->withToken($this->trainerToken)
            ->postJson('/api/branches/update-order', [
                'orders' => [
                    ['id' => 1, 'order' => 5],
                ],
            ]);

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_update_branch_order(): void
    {
        Branch::factory()->create(['id' => 1, 'order' => 1]);

        $response = $this->withToken($this->userToken)
            ->postJson('/api/branches/update-order', [
                'orders' => [
                    ['id' => 1, 'order' => 5],
                ],
            ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_update_branch_order(): void
    {
        Branch::factory()->create(['id' => 1, 'order' => 1]);

        $response = $this->postJson('/api/branches/update-order', [
            'orders' => [
                ['id' => 1, 'order' => 5],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_branch_update_order_requires_orders_array(): void
    {
        $response = $this->withToken($this->adminToken)
            ->postJson('/api/branches/update-order', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders']);
    }

    public function test_branch_update_order_items_must_have_id(): void
    {
        $response = $this->withToken($this->adminToken)
            ->postJson('/api/branches/update-order', [
                'orders' => [
                    ['order' => 1],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.id']);
    }

    public function test_branch_update_order_items_must_have_order(): void
    {
        $response = $this->withToken($this->adminToken)
            ->postJson('/api/branches/update-order', [
                'orders' => [
                    ['id' => 1],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.order']);
    }

    public function test_branch_update_order_id_must_exist(): void
    {
        Branch::factory()->create(['id' => 1, 'order' => 1]);

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/branches/update-order', [
                'orders' => [
                    ['id' => 999, 'order' => 1],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.id']);
    }

    public function test_branch_update_order_must_be_integer(): void
    {
        Branch::factory()->create(['id' => 1, 'order' => 1]);

        $response = $this->withToken($this->adminToken)
            ->postJson('/api/branches/update-order', [
                'orders' => [
                    ['id' => 1, 'order' => 'abc'],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.order']);
    }
}
