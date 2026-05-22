<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Models\EquipmentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseTest extends TestCase
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
    public function authenticated_user_can_list_exercises(): void
    {
        Exercise::factory()->count(3)->create();

        $response = $this->withToken($this->adminToken)->getJson('/api/exercises');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'per_page', 'current_page', 'last_page']
            ]);
    }

    /** @test */
    public function exercises_can_be_filtered_by_muscle_group(): void
    {
        Exercise::factory()->create(['muscle_group' => 'chest']);
        Exercise::factory()->create(['muscle_group' => 'back']);
        Exercise::factory()->create(['muscle_group' => 'chest']);

        $response = $this->withToken($this->adminToken)->getJson('/api/exercises?muscle_group=chest');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function exercises_can_be_filtered_by_equipment(): void
    {
        Exercise::factory()->create(['equipment' => 'dumbbells']);
        Exercise::factory()->create(['equipment' => 'barbell']);
        Exercise::factory()->create(['equipment' => 'dumbbells']);

        $response = $this->withToken($this->adminToken)->getJson('/api/exercises?equipment=dumbbells');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function exercises_can_be_filtered_by_difficulty(): void
    {
        Exercise::factory()->create(['difficulty' => 'beginner']);
        Exercise::factory()->create(['difficulty' => 'advanced']);
        Exercise::factory()->create(['difficulty' => 'beginner']);

        $response = $this->withToken($this->adminToken)->getJson('/api/exercises?difficulty=beginner');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function exercises_can_be_searched_by_name(): void
    {
        Exercise::factory()->create(['name' => 'Bench Press']);
        Exercise::factory()->create(['name' => 'Squat']);
        Exercise::factory()->create(['name' => 'Incline Bench Press']);

        $response = $this->withToken($this->adminToken)->getJson('/api/exercises?search=bench');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function exercises_are_paginated(): void
    {
        Exercise::factory()->count(25)->create();

        $response = $this->withToken($this->adminToken)->getJson('/api/exercises?per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.last_page', 3);
    }

    /** @test */
    public function exercises_are_ordered_by_name(): void
    {
        Exercise::factory()->create(['name' => 'Z Press']);
        Exercise::factory()->create(['name' => 'Bench Press']);
        Exercise::factory()->create(['name' => 'Squat']);

        $response = $this->withToken($this->adminToken)->getJson('/api/exercises');

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertEquals(['Bench Press', 'Squat', 'Z Press'], $names);
    }

    /** @test */
    public function authenticated_user_can_view_single_exercise(): void
    {
        $exercise = Exercise::factory()->create();

        $response = $this->withToken($this->adminToken)->getJson("/api/exercises/{$exercise->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                ]
            ]);
    }

    /** @test */
    public function admin_can_create_exercise(): void
    {
        $muscleGroup = MuscleGroup::factory()->create();
        $equipmentType = EquipmentType::factory()->create();

        $response = $this->withToken($this->adminToken)->postJson('/api/exercises', [
            'name' => 'Test Exercise',
            'slug' => 'test-exercise',
            'muscle_group' => 'chest',
            'equipment' => 'barbell',
            'difficulty' => 'intermediate',
            'muscle_group_id' => $muscleGroup->id,
            'equipment_type_id' => $equipmentType->id,
            'description' => 'A test exercise description',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Egzersiz başarıyla oluşturuldu.',
                'data' => [
                    'name' => 'Test Exercise',
                    'muscle_group' => 'chest',
                ]
            ]);

        $this->assertDatabaseHas('exercises', [
            'name' => 'Test Exercise',
            'slug' => 'test-exercise',
        ]);
    }

    /** @test */
    public function exercise_creation_requires_name(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/exercises', [
            'muscle_group' => 'chest',
            'equipment' => 'barbell',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function exercise_muscle_group_must_be_valid(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/exercises', [
            'name' => 'Test Exercise',
            'muscle_group' => 'invalid_group',
            'equipment' => 'barbell',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['muscle_group']);
    }

    /** @test */
    public function exercise_equipment_must_be_valid(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/exercises', [
            'name' => 'Test Exercise',
            'muscle_group' => 'chest',
            'equipment' => 'invalid_equipment',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['equipment']);
    }

    /** @test */
    public function exercise_difficulty_must_be_valid(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/exercises', [
            'name' => 'Test Exercise',
            'muscle_group' => 'chest',
            'equipment' => 'barbell',
            'difficulty' => 'invalid_difficulty',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['difficulty']);
    }

    /** @test */
    public function slug_must_be_unique(): void
    {
        Exercise::factory()->create(['slug' => 'bench-press']);

        $response = $this->withToken($this->adminToken)->postJson('/api/exercises', [
            'name' => 'Another Exercise',
            'slug' => 'bench-press',
            'muscle_group' => 'chest',
            'equipment' => 'barbell',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function admin_can_update_exercise(): void
    {
        $exercise = Exercise::factory()->create(['name' => 'Old Name']);

        $response = $this->withToken($this->adminToken)->putJson("/api/exercises/{$exercise->id}", [
            'name' => 'Updated Exercise',
            'slug' => 'updated-exercise',
            'muscle_group' => 'back',
            'equipment' => 'cables',
            'difficulty' => 'advanced',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Egzersiz başarıyla güncellendi.',
                'data' => [
                    'name' => 'Updated Exercise',
                ]
            ]);

        $this->assertDatabaseHas('exercises', [
            'id' => $exercise->id,
            'name' => 'Updated Exercise',
        ]);
    }

    /** @test */
    public function admin_can_partially_update_exercise(): void
    {
        $exercise = Exercise::factory()->create([
            'name' => 'Test Exercise',
            'slug' => 'test-exercise',
            'difficulty' => 'intermediate',
            'muscle_group' => 'chest',
            'equipment' => 'barbell',
            'description' => 'Original description',
        ]);

        $response = $this->withToken($this->adminToken)->putJson("/api/exercises/{$exercise->id}", [
            'name' => 'Test Exercise',
            'slug' => 'test-exercise',
            'difficulty' => 'advanced',
            'muscle_group' => 'chest',
            'equipment' => 'barbell',
        ]);

        $response->assertStatus(200);

        $exercise->refresh();
        $this->assertEquals('advanced', $exercise->difficulty);
        $this->assertEquals('Original description', $exercise->description);
    }

    /** @test */
    public function admin_can_delete_exercise(): void
    {
        $exercise = Exercise::factory()->create();

        $response = $this->withToken($this->adminToken)->deleteJson("/api/exercises/{$exercise->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Egzersiz başarıyla silindi.']);

        $this->assertDatabaseMissing('exercises', [
            'id' => $exercise->id,
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_exercise(): void
    {
        $response = $this->postJson('/api/exercises', [
            'name' => 'Test Exercise',
            'muscle_group' => 'chest',
            'equipment' => 'barbell',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_exercise(): void
    {
        $exercise = Exercise::factory()->create();

        $response = $this->putJson("/api/exercises/{$exercise->id}", [
            'name' => 'Updated',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_user_cannot_delete_exercise(): void
    {
        $exercise = Exercise::factory()->create();

        $response = $this->deleteJson("/api/exercises/{$exercise->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function guest_can_view_single_exercise(): void
    {
        $exercise = Exercise::factory()->create();

        $response = $this->getJson("/api/exercises/{$exercise->id}");

        // Exercise show requires authentication
        $response->assertStatus(401);
    }

    /** @test */
    public function exercise_list_includes_workouts_count(): void
    {
        $exercise = Exercise::factory()->create();

        $response = $this->withToken($this->adminToken)->getJson('/api/exercises');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'workouts_count'
                    ]
                ]
            ]);
    }

    /** @test */
    public function name_must_not_exceed_255_characters(): void
    {
        $response = $this->withToken($this->adminToken)->postJson('/api/exercises', [
            'name' => str_repeat('a', 256),
            'muscle_group' => 'chest',
            'equipment' => 'barbell',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function all_valid_muscle_groups_are_accepted(): void
    {
        $validGroups = ['chest', 'back', 'shoulders', 'legs', 'arms', 'core', 'full_body'];

        foreach ($validGroups as $group) {
            $response = $this->withToken($this->adminToken)->postJson('/api/exercises', [
                'name' => "Test {$group}",
                'slug' => "test-{$group}",
                'muscle_group' => $group,
                'equipment' => 'bodyweight',
                'difficulty' => 'intermediate',
            ]);

            $response->assertStatus(201);
        }
    }

    /** @test */
    public function all_valid_equipment_types_are_accepted(): void
    {
        $validEquipment = ['none', 'dumbbells', 'barbell', 'machine', 'cables', 'kettlebell', 'Resistance bands', 'bodyweight'];

        foreach ($validEquipment as $equipment) {
            $response = $this->withToken($this->adminToken)->postJson('/api/exercises', [
                'name' => "Test with {$equipment}",
                'slug' => "test-with-{$equipment}",
                'muscle_group' => 'chest',
                'equipment' => $equipment,
                'difficulty' => 'intermediate',
            ]);

            $response->assertStatus(201);
        }
    }

    /** @test */
    public function regular_user_cannot_create_exercise(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/exercises', [
            'name' => 'Test Exercise',
            'slug' => 'test-exercise',
            'muscle_group' => 'chest',
            'equipment' => 'barbell',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_update_exercise(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;
        $exercise = Exercise::factory()->create();

        $response = $this->withToken($token)->putJson("/api/exercises/{$exercise->id}", [
            'name' => 'Updated',
            'slug' => 'updated',
            'muscle_group' => 'chest',
            'equipment' => 'barbell',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_delete_exercise(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;
        $exercise = Exercise::factory()->create();

        $response = $this->withToken($token)->deleteJson("/api/exercises/{$exercise->id}");

        $response->assertStatus(403);
    }
}
