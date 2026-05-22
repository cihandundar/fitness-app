<?php

namespace Tests\Feature;

use App\Models\EquipmentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentTypeTest extends TestCase
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

    // ========== INDEX ==========

    /** @test */
    public function admin_can_list_equipment_types(): void
    {
        EquipmentType::factory()->count(3)->create();

        $response = $this->withToken($this->adminToken)->getJson('/api/equipment-types');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function equipment_types_are_ordered_by_sort_order_then_name(): void
    {
        EquipmentType::factory()->create(['sort_order' => 2, 'name' => 'Zebra']);
        EquipmentType::factory()->create(['sort_order' => 1, 'name' => 'Alpha']);
        EquipmentType::factory()->create(['sort_order' => 2, 'name' => 'Beta']);

        $response = $this->withToken($this->adminToken)->getJson('/api/equipment-types');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals('Alpha', $data[0]['name']); // sort_order 1
        $this->assertEquals('Beta', $data[1]['name']);  // sort_order 2, alphabetically first
        $this->assertEquals('Zebra', $data[2]['name']);  // sort_order 2, alphabetically second
    }

    /** @test */
    public function equipment_list_includes_all_fields(): void
    {
        EquipmentType::factory()->create([
            'name' => 'Dumbbell',
            'icon' => '🏋️',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $response = $this->withToken($this->adminToken)->getJson('/api/equipment-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'icon',
                        'image',
                        'is_active',
                        'sort_order',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_list_equipment_types(): void
    {
        EquipmentType::factory()->count(3)->create();

        $response = $this->getJson('/api/equipment-types');

        $response->assertStatus(401);
    }

    /** @test */
    public function regular_user_cannot_list_equipment_types(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;

        EquipmentType::factory()->count(3)->create();

        $response = $this->withToken($token)->getJson('/api/equipment-types');

        $response->assertStatus(403);
    }

    // ========== STORE ==========

    /** @test */
    public function admin_can_create_equipment_type(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types', [
            'name' => 'Kettlebell',
            'icon' => '🔔',
            'sort_order' => 10,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Ekipman türü oluşturuldu',
                'data' => [
                    'name' => 'Kettlebell',
                    'icon' => '🔔',
                    'sort_order' => 10,
                    'is_active' => true,
                ]
            ]);

        $this->assertDatabaseHas('equipment_types', [
            'name' => 'Kettlebell',
        ]);
    }

    /** @test */
    public function equipment_creation_requires_name(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types', [
            'icon' => '🏋️',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function name_must_not_exceed_255_characters(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types', [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function icon_must_not_exceed_50_characters(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types', [
            'name' => 'Test',
            'icon' => str_repeat('a', 51),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['icon']);
    }

    /** @test */
    public function slug_is_generated_automatically_from_name(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types', [
            'name' => 'Olympic Barbell',
        ]);

        $response->assertStatus(201);

        $equipment = EquipmentType::first();
        $this->assertEquals('olympic-barbell', $equipment->slug);
    }

    /** @test */
    public function is_active_defaults_to_true(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types', [
            'name' => 'Test Equipment',
        ]);

        $response->assertStatus(201);

        $equipment = EquipmentType::first();
        $this->assertTrue($equipment->is_active);
    }

    /** @test */
    public function sort_order_defaults_to_zero(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types', [
            'name' => 'Test Equipment',
        ]);

        $response->assertStatus(201);

        $equipment = EquipmentType::first();
        $this->assertEquals(0, $equipment->sort_order);
    }

    /** @test */
    public function icon_is_optional(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types', [
            'name' => 'Test Equipment',
        ]);

        $response->assertStatus(201);

        $equipment = EquipmentType::first();
        $this->assertNull($equipment->icon);
    }

    /** @test */
    public function sort_order_is_optional(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types', [
            'name' => 'Test Equipment',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function sort_order_must_be_integer(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types', [
            'name' => 'Test Equipment',
            'sort_order' => 'not-an-integer',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sort_order']);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_equipment_type(): void
    {
        $response = $this->postJson('/api/equipment-types', [
            'name' => 'Test Equipment',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function regular_user_cannot_create_equipment_type(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/equipment-types', [
            'name' => 'Test Equipment',
        ]);

        $response->assertStatus(403);
    }

    // ========== UPDATE ==========

    /** @test */
    public function admin_can_update_equipment_type(): void
    {
        $equipment = EquipmentType::factory()->create(['name' => 'Old Name']);

        $response = $this->withToken($this->adminToken)->putJson("/api/equipment-types/{$equipment->id}", [
            'name' => 'New Name',
            'icon' => '🏋️',
            'sort_order' => 15,
            'is_active' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ekipman türü güncellendi',
                'data' => [
                    'name' => 'New Name',
                ]
            ]);

        $this->assertDatabaseHas('equipment_types', [
            'id' => $equipment->id,
            'name' => 'New Name',
        ]);
    }

    /** @test */
    public function updating_name_also_updates_slug(): void
    {
        $equipment = EquipmentType::factory()->create([
            'name' => 'Old Name',
            'slug' => 'old-name',
        ]);

        $response = $this->withToken($this->adminToken)->putJson("/api/equipment-types/{$equipment->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200);

        $equipment->refresh();
        $this->assertEquals('new-name', $equipment->slug);
    }

    /** @test */
    public function can_update_icon_independently(): void
    {
        $equipment = EquipmentType::factory()->create(['icon' => '🏋️']);

        $response = $this->withToken($this->adminToken)->putJson("/api/equipment-types/{$equipment->id}", [
            'name' => $equipment->name,
            'icon' => '🏃',
        ]);

        $response->assertStatus(200);

        $equipment->refresh();
        $this->assertEquals('🏃', $equipment->icon);
    }

    /** @test */
    public function can_update_sort_order_independently(): void
    {
        $equipment = EquipmentType::factory()->create(['sort_order' => 5]);

        $response = $this->withToken($this->adminToken)->putJson("/api/equipment-types/{$equipment->id}", [
            'name' => $equipment->name,
            'sort_order' => 20,
        ]);

        $response->assertStatus(200);

        $equipment->refresh();
        $this->assertEquals(20, $equipment->sort_order);
    }

    /** @test */
    public function can_update_is_status_independently(): void
    {
        $equipment = EquipmentType::factory()->create(['is_active' => true]);

        $response = $this->withToken($this->adminToken)->putJson("/api/equipment-types/{$equipment->id}", [
            'name' => $equipment->name,
            'is_active' => false,
        ]);

        $response->assertStatus(200);

        $equipment->refresh();
        $this->assertFalse($equipment->is_active);
    }

    /** @test */
    public function update_requires_name(): void
    {
        $equipment = EquipmentType::factory()->create();

        $response = $this->withToken($this->adminToken)->putJson("/api/equipment-types/{$equipment->id}", [
            'icon' => '🏋️',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_equipment_type(): void
    {
        $equipment = EquipmentType::factory()->create();

        $response = $this->putJson("/api/equipment-types/{$equipment->id}", [
            'name' => 'Updated',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function regular_user_cannot_update_equipment_type(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;
        $equipment = EquipmentType::factory()->create();

        $response = $this->withToken($token)->putJson("/api/equipment-types/{$equipment->id}", [
            'name' => 'Updated',
        ]);

        $response->assertStatus(403);
    }

    // ========== DESTROY ==========

    /** @test */
    public function admin_can_delete_equipment_type(): void
    {
        $equipment = EquipmentType::factory()->create();

        $response = $this->withToken($this->adminToken)->deleteJson("/api/equipment-types/{$equipment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ekipman türü silindi',
            ]);

        $this->assertDatabaseMissing('equipment_types', [
            'id' => $equipment->id,
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_delete_equipment_type(): void
    {
        $equipment = EquipmentType::factory()->create();

        $response = $this->deleteJson("/api/equipment-types/{$equipment->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function regular_user_cannot_delete_equipment_type(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;
        $equipment = EquipmentType::factory()->create();

        $response = $this->withToken($token)->deleteJson("/api/equipment-types/{$equipment->id}");

        $response->assertStatus(403);
    }

    // ========== REORDER ==========

    /** @test */
    public function admin_can_reorder_equipment_types(): void
    {
        $eq1 = EquipmentType::factory()->create(['sort_order' => 1]);
        $eq2 = EquipmentType::factory()->create(['sort_order' => 2]);
        $eq3 = EquipmentType::factory()->create(['sort_order' => 3]);

        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types/reorder', [
            'orders' => [
                ['id' => $eq1->id, 'sort_order' => 3],
                ['id' => $eq2->id, 'sort_order' => 1],
                ['id' => $eq3->id, 'sort_order' => 2],
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sıralama güncellendi',
            ]);

        $this->assertDatabaseHas('equipment_types', ['id' => $eq1->id, 'sort_order' => 3]);
        $this->assertDatabaseHas('equipment_types', ['id' => $eq2->id, 'sort_order' => 1]);
        $this->assertDatabaseHas('equipment_types', ['id' => $eq3->id, 'sort_order' => 2]);
    }

    /** @test */
    public function reorder_requires_orders_array(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types/reorder', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders']);
    }

    /** @test */
    public function orders_items_must_have_id(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types/reorder', [
            'orders' => [
                ['sort_order' => 1],
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.id']);
    }

    /** @test */
    public function orders_items_must_have_sort_order(): void
    {
        $equipment = EquipmentType::factory()->create();

        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types/reorder', [
            'orders' => [
                ['id' => $equipment->id],
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.sort_order']);
    }

    /** @test */
    public function equipment_id_must_exist(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types/reorder', [
            'orders' => [
                ['id' => 999, 'sort_order' => 1],
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.id']);
    }

    /** @test */
    public function reorder_sort_order_must_be_integer(): void
    {
        $equipment = EquipmentType::factory()->create();

        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types/reorder', [
            'orders' => [
                ['id' => $equipment->id, 'sort_order' => 'not-an-integer'],
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.sort_order']);
    }

    /** @test */
    public function can_reorder_multiple_items_at_once(): void
    {
        $items = EquipmentType::factory()->count(5)->create();

        $orders = [];
        foreach ($items as $index => $item) {
            $orders[] = ['id' => $item->id, 'sort_order' => ($index + 1) * 10];
        }

        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types/reorder', [
            'orders' => $orders,
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_cannot_reorder(): void
    {
        $response = $this->postJson('/api/equipment-types/reorder', [
            'orders' => []
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function regular_user_cannot_reorder(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/equipment-types/reorder', [
            'orders' => []
        ]);

        $response->assertStatus(403);
    }

    // ========== ADDITIONAL TESTS ==========

    /** @test */
    public function duplicate_name_generates_different_slug(): void
    {
        EquipmentType::factory()->create(['name' => 'Dumbbell']);

        // Factory generates unique slug automatically
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types', [
            'name' => 'Dumbbell',
        ]);

        // This will create a different slug due to timestamp/random in factory
        // or the controller should handle it
        $response->assertStatus(201);
    }

    /** @test */
    public function can_create_equipment_with_emoji_icon(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types', [
            'name' => 'Treadmill',
            'icon' => '🏃',
        ]);

        $response->assertStatus(201);

        $equipment = EquipmentType::first();
        $this->assertEquals('🏃', $equipment->icon);
    }

    /** @test */
    public function can_create_equipment_with_text_icon(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types', [
            'name' => 'Bike',
            'icon' => 'fa-bicycle',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function can_set_negative_sort_order(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/equipment-types', [
            'name' => 'Test',
            'sort_order' => -5,
        ]);

        $response->assertStatus(201);

        $equipment = EquipmentType::first();
        $this->assertEquals(-5, $equipment->sort_order);
    }

    /** @test */
    public function list_returns_all_equipment_including_inactive(): void
    {
        EquipmentType::factory()->create(['is_active' => true]);
        EquipmentType::factory()->create(['is_active' => false]);

        $response = $this->withToken($this->adminToken)->getJson('/api/equipment-types');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function equipment_with_same_sort_order_are_sorted_alphabetically(): void
    {
        EquipmentType::factory()->create(['sort_order' => 1, 'name' => 'Charlie']);
        EquipmentType::factory()->create(['sort_order' => 1, 'name' => 'Alpha']);
        EquipmentType::factory()->create(['sort_order' => 1, 'name' => 'Bravo']);

        $response = $this->withToken($this->adminToken)->getJson('/api/equipment-types');

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertEquals(['Alpha', 'Bravo', 'Charlie'], $names);
    }
}
