<?php

namespace Tests\Feature;

use App\Models\EquipmentType;
use App\Models\MuscleGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferenceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    // ========== GET PREFERENCES ==========

    /** @test */
    public function authenticated_user_can_get_their_preferences(): void
    {
        $muscleGroup = MuscleGroup::factory()->create();
        $equipment = EquipmentType::factory()->create();

        $this->user->targetGroups()->attach($muscleGroup->id);
        $this->user->equipments()->attach($equipment->id);

        $response = $this->withToken($this->token)->getJson('/api/user/preferences');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'target_groups' => [],
                    'equipment' => [],
                ]
            ])
            ->assertJsonStructure([
                'data' => [
                    'target_groups' => [
                        '*' => ['id', 'name', 'slug', 'icon', 'color', 'is_active', 'sort_order']
                    ],
                    'equipment' => [
                        '*' => ['id', 'name', 'slug', 'icon', 'is_active', 'sort_order']
                    ]
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_get_preferences(): void
    {
        $response = $this->getJson('/api/user/preferences');

        $response->assertStatus(401);
    }

    /** @test */
    public function preferences_are_ordered_by_sort_order(): void
    {
        $mg1 = MuscleGroup::factory()->create(['sort_order' => 3]);
        $mg2 = MuscleGroup::factory()->create(['sort_order' => 1]);
        $mg3 = MuscleGroup::factory()->create(['sort_order' => 2]);

        $this->user->targetGroups()->attach([$mg1->id, $mg2->id, $mg3->id]);

        $response = $this->withToken($this->token)->getJson('/api/user/preferences');

        $response->assertStatus(200);

        $orders = collect($response->json('data.target_groups'))->pluck('sort_order')->toArray();
        $this->assertEquals([1, 2, 3], $orders);
    }

    /** @test */
    public function empty_preferences_return_empty_arrays(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/user/preferences');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data.target_groups')
            ->assertJsonCount(0, 'data.equipment');
    }

    // ========== UPDATE PREFERENCES ==========

    /** @test */
    public function authenticated_user_can_update_their_preferences(): void
    {
        $muscleGroup = MuscleGroup::factory()->create();
        $equipment = EquipmentType::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'target_groups' => [$muscleGroup->id],
            'equipment' => [$equipment->id],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Tercihler güncellendi',
            ]);

        $this->assertDatabaseHas('user_target_groups', [
            'user_id' => $this->user->id,
            'muscle_group_id' => $muscleGroup->id,
        ]);

        $this->assertDatabaseHas('user_equipment', [
            'user_id' => $this->user->id,
            'equipment_type_id' => $equipment->id,
        ]);
    }

    /** @test */
    public function can_update_only_target_groups(): void
    {
        $muscleGroup = MuscleGroup::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'target_groups' => [$muscleGroup->id],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_target_groups', [
            'user_id' => $this->user->id,
            'muscle_group_id' => $muscleGroup->id,
        ]);
    }

    /** @test */
    public function can_update_only_equipment(): void
    {
        $equipment = EquipmentType::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'equipment' => [$equipment->id],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_equipment', [
            'user_id' => $this->user->id,
            'equipment_type_id' => $equipment->id,
        ]);
    }

    /** @test */
    public function can_update_multiple_target_groups(): void
    {
        $mg1 = MuscleGroup::factory()->create();
        $mg2 = MuscleGroup::factory()->create();
        $mg3 = MuscleGroup::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'target_groups' => [$mg1->id, $mg2->id, $mg3->id],
        ]);

        $response->assertStatus(200);

        $this->assertEquals(3, $this->user->targetGroups()->count());
    }

    /** @test */
    public function can_update_multiple_equipment(): void
    {
        $eq1 = EquipmentType::factory()->create();
        $eq2 = EquipmentType::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'equipment' => [$eq1->id, $eq2->id],
        ]);

        $response->assertStatus(200);

        $this->assertEquals(2, $this->user->equipments()->count());
    }

    /** @test */
    public function update_replaces_existing_preferences(): void
    {
        $mg1 = MuscleGroup::factory()->create();
        $mg2 = MuscleGroup::factory()->create();

        // Set initial preferences
        $this->user->targetGroups()->attach([$mg1->id]);

        // Update with new preferences
        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'target_groups' => [$mg2->id],
        ]);

        $response->assertStatus(200);

        $this->assertEquals(1, $this->user->targetGroups()->count());
        $this->assertEquals($mg2->id, $this->user->targetGroups()->first()->id);
    }

    /** @test */
    public function can_clear_target_groups_by_sending_empty_array(): void
    {
        $muscleGroup = MuscleGroup::factory()->create();
        $this->user->targetGroups()->attach($muscleGroup->id);

        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'target_groups' => [],
        ]);

        $response->assertStatus(200);

        $this->assertEquals(0, $this->user->targetGroups()->count());
    }

    /** @test */
    public function can_clear_equipment_by_sending_empty_array(): void
    {
        $equipment = EquipmentType::factory()->create();
        $this->user->equipments()->attach($equipment->id);

        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'equipment' => [],
        ]);

        $response->assertStatus(200);

        $this->assertEquals(0, $this->user->equipments()->count());
    }

    /** @test */
    public function target_groups_must_be_an_array(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'target_groups' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['target_groups']);
    }

    /** @test */
    public function equipment_must_be_an_array(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'equipment' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['equipment']);
    }

    /** @test */
    public function target_group_ids_must_exist(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'target_groups' => [999],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['target_groups.0']);
    }

    /** @test */
    public function equipment_ids_must_exist(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'equipment' => [999],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['equipment.0']);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_preferences(): void
    {
        $muscleGroup = MuscleGroup::factory()->create();

        $response = $this->postJson('/api/user/preferences', [
            'target_groups' => [$muscleGroup->id],
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_only_update_their_own_preferences(): void
    {
        $otherUser = User::factory()->create();
        $otherToken = $otherUser->createToken('test-token')->plainTextToken;

        $muscleGroup = MuscleGroup::factory()->create();

        $response = $this->withToken($otherToken)->postJson('/api/user/preferences', [
            'target_groups' => [$muscleGroup->id],
        ]);

        $response->assertStatus(200);

        // Other user's preferences should be saved, not $this->user's
        $this->assertEquals(0, $this->user->targetGroups()->count());
        $this->assertEquals(1, $otherUser->targetGroups()->count());
    }

    /** @test */
    public function updated_preferences_are_returned_in_response(): void
    {
        $muscleGroup = MuscleGroup::factory()->create(['sort_order' => 5]);
        $equipment = EquipmentType::factory()->create(['sort_order' => 3]);

        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'target_groups' => [$muscleGroup->id],
            'equipment' => [$equipment->id],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'target_groups' => [
                        '*' => ['id', 'name', 'slug', 'color', 'icon']
                    ],
                    'equipment' => [
                        '*' => ['id', 'name', 'slug', 'icon']
                    ]
                ]
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data['target_groups']);
        $this->assertCount(1, $data['equipment']);
        $this->assertEquals($muscleGroup->id, $data['target_groups'][0]['id']);
        $this->assertEquals($equipment->id, $data['equipment'][0]['id']);
    }

    // ========== GET AVAILABLE OPTIONS ==========

    /** @test */
    public function authenticated_user_can_get_available_options(): void
    {
        MuscleGroup::factory()->create(['is_active' => true]);
        MuscleGroup::factory()->create(['is_active' => true]);
        MuscleGroup::factory()->create(['is_active' => false]); // Inactive

        EquipmentType::factory()->create(['is_active' => true]);
        EquipmentType::factory()->create(['is_active' => false]); // Inactive

        $response = $this->withToken($this->token)->getJson('/api/user/available-options');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'muscle_groups' => [
                        '*' => ['id', 'name', 'slug', 'icon', 'color', 'is_active', 'sort_order']
                    ],
                    'equipment_types' => [
                        '*' => ['id', 'name', 'slug', 'icon', 'is_active', 'sort_order']
                    ]
                ]
            ]);
    }

    /** @test */
    public function available_options_only_shows_active_items(): void
    {
        MuscleGroup::factory()->count(3)->create(['is_active' => true]);
        MuscleGroup::factory()->count(2)->create(['is_active' => false]);

        EquipmentType::factory()->count(4)->create(['is_active' => true]);
        EquipmentType::factory()->count(3)->create(['is_active' => false]);

        $response = $this->withToken($this->token)->getJson('/api/user/available-options');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.muscle_groups')
            ->assertJsonCount(4, 'data.equipment_types');
    }

    /** @test */
    public function available_options_are_ordered_by_sort_order(): void
    {
        MuscleGroup::factory()->create(['sort_order' => 3, 'is_active' => true]);
        MuscleGroup::factory()->create(['sort_order' => 1, 'is_active' => true]);
        MuscleGroup::factory()->create(['sort_order' => 2, 'is_active' => true]);

        $response = $this->withToken($this->token)->getJson('/api/user/available-options');

        $response->assertStatus(200);

        $orders = collect($response->json('data.muscle_groups'))->pluck('sort_order')->toArray();
        $this->assertEquals([1, 2, 3], $orders);
    }

    /** @test */
    public function unauthenticated_user_cannot_get_available_options(): void
    {
        $response = $this->getJson('/api/user/available-options');

        $response->assertStatus(401);
    }

    /** @test */
    public function empty_options_return_empty_arrays(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/user/available-options');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data.muscle_groups')
            ->assertJsonCount(0, 'data.equipment_types');
    }

    // ========== ADDITIONAL TESTS ==========

    /** @test */
    public function preferences_persist_across_requests(): void
    {
        $muscleGroup = MuscleGroup::factory()->create();

        // Set preferences
        $this->withToken($this->token)->postJson('/api/user/preferences', [
            'target_groups' => [$muscleGroup->id],
        ])->assertStatus(200);

        // Get preferences
        $response = $this->withToken($this->token)->getJson('/api/user/preferences');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.target_groups')
            ->assertJsonPath('data.target_groups.0.id', $muscleGroup->id);
    }

    /** @test */
    public function sending_null_for_optional_fields_is_allowed(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'target_groups' => null,
            'equipment' => null,
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_have_both_target_groups_and_equipment(): void
    {
        $muscleGroup = MuscleGroup::factory()->create();
        $equipment = EquipmentType::factory()->create();

        $this->user->targetGroups()->attach($muscleGroup->id);
        $this->user->equipments()->attach($equipment->id);

        $response = $this->withToken($this->token)->getJson('/api/user/preferences');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.target_groups')
            ->assertJsonCount(1, 'data.equipment');
    }

    /** @test */
    public function invalid_target_group_id_in_array_fails_validation(): void
    {
        $validGroup = MuscleGroup::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'target_groups' => [$validGroup->id, 999],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['target_groups.1']);
    }

    /** @test */
    public function invalid_equipment_id_in_array_fails_validation(): void
    {
        $validEquipment = EquipmentType::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/user/preferences', [
            'equipment' => [$validEquipment->id, 999],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['equipment.1']);
    }
}
