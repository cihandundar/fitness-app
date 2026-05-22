<?php

namespace Tests\Feature;

use App\Models\MuscleGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MuscleGroupTest extends TestCase
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
    public function admin_can_list_muscle_groups(): void
    {
        MuscleGroup::factory()->count(3)->create();

        $response = $this->withToken($this->adminToken)->getJson('/api/muscle-groups');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function muscle_groups_are_ordered_by_sort_order_then_name(): void
    {
        MuscleGroup::factory()->create(['sort_order' => 2, 'name' => 'Zebra']);
        MuscleGroup::factory()->create(['sort_order' => 1, 'name' => 'Alpha']);
        MuscleGroup::factory()->create(['sort_order' => 2, 'name' => 'Beta']);

        $response = $this->withToken($this->adminToken)->getJson('/api/muscle-groups');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals('Alpha', $data[0]['name']); // sort_order 1
        $this->assertEquals('Beta', $data[1]['name']);  // sort_order 2, alphabetically first
        $this->assertEquals('Zebra', $data[2]['name']);  // sort_order 2, alphabetically second
    }

    /** @test */
    public function muscle_group_list_includes_all_fields(): void
    {
        MuscleGroup::factory()->create([
            'name' => 'Göğüs',
            'icon' => '💪',
            'color' => 'red',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $response = $this->withToken($this->adminToken)->getJson('/api/muscle-groups');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'icon',
                        'color',
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
    public function unauthenticated_user_cannot_list_muscle_groups(): void
    {
        MuscleGroup::factory()->count(3)->create();

        $response = $this->getJson('/api/muscle-groups');

        $response->assertStatus(401);
    }

    /** @test */
    public function regular_user_cannot_list_muscle_groups(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;

        MuscleGroup::factory()->count(3)->create();

        $response = $this->withToken($token)->getJson('/api/muscle-groups');

        $response->assertStatus(403);
    }

    // ========== STORE ==========

    /** @test */
    public function admin_can_create_muscle_group(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Biceps',
            'icon' => '💪',
            'color' => 'blue',
            'sort_order' => 10,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Kas grubu oluşturuldu',
                'data' => [
                    'name' => 'Biceps',
                    'icon' => '💪',
                    'color' => 'blue',
                    'sort_order' => 10,
                    'is_active' => true,
                ]
            ]);

        $this->assertDatabaseHas('muscle_groups', [
            'name' => 'Biceps',
        ]);
    }

    /** @test */
    public function muscle_group_creation_requires_name(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'icon' => '💪',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function name_must_not_exceed_255_characters(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function icon_must_not_exceed_50_characters(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Test',
            'icon' => str_repeat('a', 51),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['icon']);
    }

    /** @test */
    public function color_must_not_exceed_50_characters(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Test',
            'color' => str_repeat('a', 51),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['color']);
    }

    /** @test */
    public function slug_is_generated_automatically_from_name(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Upper Chest',
        ]);

        $response->assertStatus(201);

        $group = MuscleGroup::first();
        $this->assertEquals('upper-chest', $group->slug);
    }

    /** @test */
    public function is_active_defaults_to_true(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Test Group',
        ]);

        $response->assertStatus(201);

        $group = MuscleGroup::first();
        $this->assertTrue($group->is_active);
    }

    /** @test */
    public function sort_order_defaults_to_zero(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Test Group',
        ]);

        $response->assertStatus(201);

        $group = MuscleGroup::first();
        $this->assertEquals(0, $group->sort_order);
    }

    /** @test */
    public function color_defaults_to_violet(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Test Group',
        ]);

        $response->assertStatus(201);

        $group = MuscleGroup::first();
        $this->assertEquals('violet', $group->color);
    }

    /** @test */
    public function icon_is_optional(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Test Group',
        ]);

        $response->assertStatus(201);

        $group = MuscleGroup::first();
        $this->assertNull($group->icon);
    }

    /** @test */
    public function color_is_optional(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Test Group',
            'color' => null,
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function sort_order_is_optional(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Test Group',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function sort_order_must_be_integer(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Test Group',
            'sort_order' => 'not-an-integer',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sort_order']);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_muscle_group(): void
    {
        $response = $this->postJson('/api/muscle-groups', [
            'name' => 'Test Group',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function regular_user_cannot_create_muscle_group(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/muscle-groups', [
            'name' => 'Test Group',
        ]);

        $response->assertStatus(403);
    }

    // ========== UPDATE ==========

    /** @test */
    public function admin_can_update_muscle_group(): void
    {
        $group = MuscleGroup::factory()->create(['name' => 'Old Name']);

        $response = $this->withToken($this->adminToken)->putJson("/api/muscle-groups/{$group->id}", [
            'name' => 'New Name',
            'icon' => '💪',
            'color' => 'red',
            'sort_order' => 15,
            'is_active' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kas grubu güncellendi',
                'data' => [
                    'name' => 'New Name',
                ]
            ]);

        $this->assertDatabaseHas('muscle_groups', [
            'id' => $group->id,
            'name' => 'New Name',
        ]);
    }

    /** @test */
    public function updating_name_also_updates_slug(): void
    {
        $group = MuscleGroup::factory()->create([
            'name' => 'Old Name',
            'slug' => 'old-name',
        ]);

        $response = $this->withToken($this->adminToken)->putJson("/api/muscle-groups/{$group->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200);

        $group->refresh();
        $this->assertEquals('new-name', $group->slug);
    }

    /** @test */
    public function can_update_icon_independently(): void
    {
        $group = MuscleGroup::factory()->create(['icon' => '💪']);

        $response = $this->withToken($this->adminToken)->putJson("/api/muscle-groups/{$group->id}", [
            'name' => $group->name,
            'icon' => '🏋️',
        ]);

        $response->assertStatus(200);

        $group->refresh();
        $this->assertEquals('🏋️', $group->icon);
    }

    /** @test */
    public function can_update_color_independently(): void
    {
        $group = MuscleGroup::factory()->create(['color' => 'red']);

        $response = $this->withToken($this->adminToken)->putJson("/api/muscle-groups/{$group->id}", [
            'name' => $group->name,
            'color' => 'blue',
        ]);

        $response->assertStatus(200);

        $group->refresh();
        $this->assertEquals('blue', $group->color);
    }

    /** @test */
    public function can_update_sort_order_independently(): void
    {
        $group = MuscleGroup::factory()->create(['sort_order' => 5]);

        $response = $this->withToken($this->adminToken)->putJson("/api/muscle-groups/{$group->id}", [
            'name' => $group->name,
            'sort_order' => 20,
        ]);

        $response->assertStatus(200);

        $group->refresh();
        $this->assertEquals(20, $group->sort_order);
    }

    /** @test */
    public function can_update_is_status_independently(): void
    {
        $group = MuscleGroup::factory()->create(['is_active' => true]);

        $response = $this->withToken($this->adminToken)->putJson("/api/muscle-groups/{$group->id}", [
            'name' => $group->name,
            'is_active' => false,
        ]);

        $response->assertStatus(200);

        $group->refresh();
        $this->assertFalse($group->is_active);
    }

    /** @test */
    public function update_requires_name(): void
    {
        $group = MuscleGroup::factory()->create();

        $response = $this->withToken($this->adminToken)->putJson("/api/muscle-groups/{$group->id}", [
            'icon' => '💪',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_muscle_group(): void
    {
        $group = MuscleGroup::factory()->create();

        $response = $this->putJson("/api/muscle-groups/{$group->id}", [
            'name' => 'Updated',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function regular_user_cannot_update_muscle_group(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;
        $group = MuscleGroup::factory()->create();

        $response = $this->withToken($token)->putJson("/api/muscle-groups/{$group->id}", [
            'name' => 'Updated',
        ]);

        $response->assertStatus(403);
    }

    // ========== DESTROY ==========

    /** @test */
    public function admin_can_delete_muscle_group(): void
    {
        $group = MuscleGroup::factory()->create();

        $response = $this->withToken($this->adminToken)->deleteJson("/api/muscle-groups/{$group->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kas grubu silindi',
            ]);

        $this->assertDatabaseMissing('muscle_groups', [
            'id' => $group->id,
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_delete_muscle_group(): void
    {
        $group = MuscleGroup::factory()->create();

        $response = $this->deleteJson("/api/muscle-groups/{$group->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function regular_user_cannot_delete_muscle_group(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;
        $group = MuscleGroup::factory()->create();

        $response = $this->withToken($token)->deleteJson("/api/muscle-groups/{$group->id}");

        $response->assertStatus(403);
    }

    // ========== REORDER ==========

    /** @test */
    public function admin_can_reorder_muscle_groups(): void
    {
        $mg1 = MuscleGroup::factory()->create(['sort_order' => 1]);
        $mg2 = MuscleGroup::factory()->create(['sort_order' => 2]);
        $mg3 = MuscleGroup::factory()->create(['sort_order' => 3]);

        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups/reorder', [
            'orders' => [
                ['id' => $mg1->id, 'sort_order' => 3],
                ['id' => $mg2->id, 'sort_order' => 1],
                ['id' => $mg3->id, 'sort_order' => 2],
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sıralama güncellendi',
            ]);

        $this->assertDatabaseHas('muscle_groups', ['id' => $mg1->id, 'sort_order' => 3]);
        $this->assertDatabaseHas('muscle_groups', ['id' => $mg2->id, 'sort_order' => 1]);
        $this->assertDatabaseHas('muscle_groups', ['id' => $mg3->id, 'sort_order' => 2]);
    }

    /** @test */
    public function reorder_requires_orders_array(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups/reorder', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders']);
    }

    /** @test */
    public function orders_items_must_have_id(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups/reorder', [
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
        $group = MuscleGroup::factory()->create();

        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups/reorder', [
            'orders' => [
                ['id' => $group->id],
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.sort_order']);
    }

    /** @test */
    public function muscle_group_id_must_exist(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups/reorder', [
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
        $group = MuscleGroup::factory()->create();

        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups/reorder', [
            'orders' => [
                ['id' => $group->id, 'sort_order' => 'not-an-integer'],
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orders.0.sort_order']);
    }

    /** @test */
    public function can_reorder_multiple_items_at_once(): void
    {
        $items = MuscleGroup::factory()->count(5)->create();

        $orders = [];
        foreach ($items as $index => $item) {
            $orders[] = ['id' => $item->id, 'sort_order' => ($index + 1) * 10];
        }

        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups/reorder', [
            'orders' => $orders,
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_cannot_reorder(): void
    {
        $response = $this->postJson('/api/muscle-groups/reorder', [
            'orders' => []
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function regular_user_cannot_reorder(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/muscle-groups/reorder', [
            'orders' => []
        ]);

        $response->assertStatus(403);
    }

    // ========== ADDITIONAL TESTS ==========

    /** @test */
    public function can_create_muscle_group_with_emoji_icon(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Chest',
            'icon' => '💪',
        ]);

        $response->assertStatus(201);

        $group = MuscleGroup::first();
        $this->assertEquals('💪', $group->icon);
    }

    /** @test */
    public function can_create_muscle_group_with_text_icon(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Back',
            'icon' => 'fa-dumbbell',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function can_create_muscle_group_with_color_name(): void
    {
        $colors = ['red', 'blue', 'green', 'yellow', 'purple', 'orange', 'pink', 'violet'];

        foreach ($colors as $color) {
            $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
                'name' => "Test {$color}",
                'color' => $color,
            ]);

            $response->assertStatus(201);
        }
    }

    /** @test */
    public function can_create_muscle_group_with_hex_color(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Test',
            'color' => '#FF5733',
        ]);

        $response->assertStatus(201);

        $group = MuscleGroup::first();
        $this->assertEquals('#FF5733', $group->color);
    }

    /** @test */
    public function can_set_negative_sort_order(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Test',
            'sort_order' => -5,
        ]);

        $response->assertStatus(201);

        $group = MuscleGroup::first();
        $this->assertEquals(-5, $group->sort_order);
    }

    /** @test */
    public function list_returns_all_muscle_groups_including_inactive(): void
    {
        MuscleGroup::factory()->create(['is_active' => true]);
        MuscleGroup::factory()->create(['is_active' => false]);

        $response = $this->withToken($this->adminToken)->getJson('/api/muscle-groups');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function muscle_groups_with_same_sort_order_are_sorted_alphabetically(): void
    {
        MuscleGroup::factory()->create(['sort_order' => 1, 'name' => 'Charlie']);
        MuscleGroup::factory()->create(['sort_order' => 1, 'name' => 'Alpha']);
        MuscleGroup::factory()->create(['sort_order' => 1, 'name' => 'Bravo']);

        $response = $this->withToken($this->adminToken)->getJson('/api/muscle-groups');

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertEquals(['Alpha', 'Bravo', 'Charlie'], $names);
    }

    /** @test */
    public function can_clear_icon_by_sending_empty_string(): void
    {
        $group = MuscleGroup::factory()->create(['icon' => '💪']);

        $response = $this->withToken($this->adminToken)->putJson("/api/muscle-groups/{$group->id}", [
            'name' => $group->name,
            'icon' => '',
        ]);

        $response->assertStatus(200);

        $group->refresh();
        $this->assertEmpty($group->icon);
    }

    /** @test */
    public function can_change_color_to_different_value(): void
    {
        $group = MuscleGroup::factory()->create(['color' => 'red']);

        $response = $this->withToken($this->adminToken)->putJson("/api/muscle-groups/{$group->id}", [
            'name' => $group->name,
            'color' => 'blue',
        ]);

        $response->assertStatus(200);

        $group->refresh();
        $this->assertEquals('blue', $group->color);
    }

    /** @test */
    public function duplicate_name_generates_different_slug(): void
    {
        MuscleGroup::factory()->create(['name' => 'Chest']);

        // Factory generates unique slug automatically
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Chest',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function turkish_name_generates_correct_slug(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/muscle-groups', [
            'name' => 'Ön Kol',
        ]);

        $response->assertStatus(201);

        $group = MuscleGroup::first();
        $this->assertEquals('on-kol', $group->slug);
    }
}
