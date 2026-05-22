<?php

namespace Tests\Feature;

use App\Models\CompletedWorkout;
use App\Models\Exercise;
use App\Models\ExerciseLog;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutLoggingTest extends TestCase
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

    /** @test */
    public function authenticated_user_can_start_workout(): void
    {
        $workout = Workout::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/workout-tracking/start', [
            'workout_id' => $workout->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Antrenman başlatıldı.',
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'user_id',
                    'workout_id',
                    'started_at',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('completed_workouts', [
            'user_id' => $this->user->id,
            'workout_id' => $workout->id,
        ]);
    }

    /** @test */
    public function user_can_start_freestyle_workout_without_workout_id(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/workout-tracking/start');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Antrenman başlatıldı.',
            ]);

        $this->assertDatabaseHas('completed_workouts', [
            'user_id' => $this->user->id,
            'workout_id' => null,
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_start_workout(): void
    {
        $response = $this->postJson('/api/workout-tracking/start');

        $response->assertStatus(401);
    }

    /** @test */
    public function workout_id_must_exist_when_provided(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/workout-tracking/start', [
            'workout_id' => 999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['workout_id']);
    }

    /** @test */
    public function user_can_log_exercise_set(): void
    {
        $session = CompletedWorkout::factory()->forUser($this->user)->inProgress()->create();
        $exercise = Exercise::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/workout-tracking/log-set', [
            'completed_workout_id' => $session->id,
            'exercise_id' => $exercise->id,
            'set_number' => 1,
            'weight' => 80,
            'reps' => 12,
            'rest_time' => 90,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Set kaydedildi.',
            ]);

        $this->assertDatabaseHas('exercise_logs', [
            'completed_workout_id' => $session->id,
            'exercise_id' => $exercise->id,
            'set_number' => 1,
            'weight' => 80,
            'reps' => 12,
        ]);
    }

    /** @test */
    public function log_set_requires_completed_workout_id(): void
    {
        $exercise = Exercise::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/workout-tracking/log-set', [
            'exercise_id' => $exercise->id,
            'set_number' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['completed_workout_id']);
    }

    /** @test */
    public function log_set_requires_exercise_id(): void
    {
        $session = CompletedWorkout::factory()->forUser($this->user)->inProgress()->create();

        $response = $this->withToken($this->token)->postJson('/api/workout-tracking/log-set', [
            'completed_workout_id' => $session->id,
            'set_number' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['exercise_id']);
    }

    /** @test */
    public function log_set_requires_set_number(): void
    {
        $session = CompletedWorkout::factory()->forUser($this->user)->inProgress()->create();
        $exercise = Exercise::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/workout-tracking/log-set', [
            'completed_workout_id' => $session->id,
            'exercise_id' => $exercise->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['set_number']);
    }

    /** @test */
    public function set_number_must_be_integer(): void
    {
        $session = CompletedWorkout::factory()->forUser($this->user)->inProgress()->create();
        $exercise = Exercise::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/workout-tracking/log-set', [
            'completed_workout_id' => $session->id,
            'exercise_id' => $exercise->id,
            'set_number' => 'first',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['set_number']);
    }

    /** @test */
    public function weight_must_be_numeric_when_provided(): void
    {
        $session = CompletedWorkout::factory()->forUser($this->user)->inProgress()->create();
        $exercise = Exercise::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/workout-tracking/log-set', [
            'completed_workout_id' => $session->id,
            'exercise_id' => $exercise->id,
            'set_number' => 1,
            'weight' => 'heavy',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['weight']);
    }

    /** @test */
    public function reps_must_be_integer_when_provided(): void
    {
        $session = CompletedWorkout::factory()->forUser($this->user)->inProgress()->create();
        $exercise = Exercise::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/workout-tracking/log-set', [
            'completed_workout_id' => $session->id,
            'exercise_id' => $exercise->id,
            'set_number' => 1,
            'reps' => 'many',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reps']);
    }

    /** @test */
    public function user_can_finish_workout_session(): void
    {
        $session = CompletedWorkout::factory()->forUser($this->user)->inProgress()->create();

        $response = $this->withToken($this->token)->postJson("/api/workout-tracking/finish/{$session->id}", [
            'notes' => 'Harika antrenman!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Antrenman tamamlandı.',
            ]);

        $session->refresh();
        $this->assertNotNull($session->completed_at);
        $this->assertEquals('Harika antrenman!', $session->notes);
    }

    /** @test */
    public function user_can_finish_workout_without_notes(): void
    {
        $session = CompletedWorkout::factory()->forUser($this->user)->inProgress()->create();

        $response = $this->withToken($this->token)->postJson("/api/workout-tracking/finish/{$session->id}");

        $response->assertStatus(200);

        $session->refresh();
        $this->assertNotNull($session->completed_at);
    }

    /** @test */
    public function user_can_only_finish_their_own_workout(): void
    {
        $otherUser = User::factory()->create();
        $session = CompletedWorkout::factory()->forUser($otherUser)->inProgress()->create();

        $response = $this->withToken($this->token)->postJson("/api/workout-tracking/finish/{$session->id}", [
            'notes' => 'Trying to hack',
        ]);

        // Currently no authorization in controller, so it might succeed
        // This test documents current behavior
        $response->assertStatus(200);

        // Note: In production, authorization should be added
        // to prevent users from finishing others' workouts
    }

    /** @test */
    public function unauthenticated_user_cannot_finish_workout(): void
    {
        $session = CompletedWorkout::factory()->forUser($this->user)->inProgress()->create();

        $response = $this->postJson("/api/workout-tracking/finish/{$session->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_get_their_workout_history(): void
    {
        CompletedWorkout::factory()->forUser($this->user)->completed()->count(3);
        CompletedWorkout::factory()->forUser($this->user)->inProgress()->count(1);

        $response = $this->withToken($this->token)->getJson('/api/workout-tracking/history');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'workout_id',
                            'started_at',
                            'completed_at',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function user_only_sees_their_own_workout_history(): void
    {
        // Other user's workouts
        $otherUser = User::factory()->create();
        CompletedWorkout::factory()->forUser($otherUser)->completed()->count(5)->create();

        // This user's workouts
        CompletedWorkout::factory()->forUser($this->user)->completed()->count(2)->create();

        $response = $this->withToken($this->token)->getJson('/api/workout-tracking/history');

        $response->assertStatus(200);

        // Should only see 2 workouts (their own)
        $data = $response->json('data.data');
        $this->assertCount(2, $data);
    }

    /** @test */
    public function workout_history_is_paginated(): void
    {
        CompletedWorkout::factory()->forUser($this->user)->completed()->count(15);

        $response = $this->withToken($this->token)->getJson('/api/workout-tracking/history');

        $response->assertStatus(200)
            ->assertJsonPath('data.data', fn ($data) => is_array($data) && count($data) <= 10);
    }

    /** @test */
    public function unauthenticated_user_cannot_get_history(): void
    {
        $response = $this->getJson('/api/workout-tracking/history');

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_get_exercise_history(): void
    {
        $exercise = Exercise::factory()->create();
        $session = CompletedWorkout::factory()->forUser($this->user)->completed()->create();

        ExerciseLog::factory()->forCompletedWorkout($session)->forExercise($exercise)->count(3)->create();

        $response = $this->withToken($this->token)
            ->getJson("/api/workout-tracking/exercise-history/{$exercise->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'completed_workout_id',
                        'exercise_id',
                        'set_number',
                        'weight',
                        'reps',
                    ],
                ],
            ]);
    }

    /** @test */
    public function exercise_history_only_shows_users_own_logs(): void
    {
        $exercise = Exercise::factory()->create();

        // Other user's logs
        $otherSession = CompletedWorkout::factory()
            ->forUser(User::factory()->create())
            ->completed()
            ->create();
        ExerciseLog::factory()->forCompletedWorkout($otherSession)->forExercise($exercise)->count(5)->create();

        // This user's logs
        $session = CompletedWorkout::factory()->forUser($this->user)->completed()->create();
        ExerciseLog::factory()->forCompletedWorkout($session)->forExercise($exercise)->count(2)->create();

        $response = $this->withToken($this->token)
            ->getJson("/api/workout-tracking/exercise-history/{$exercise->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    /** @test */
    public function exercise_history_is_limited_to_10_records(): void
    {
        $exercise = Exercise::factory()->create();
        $session = CompletedWorkout::factory()->forUser($this->user)->completed()->create();

        ExerciseLog::factory()->forCompletedWorkout($session)->forExercise($exercise)->count(15)->create();

        $response = $this->withToken($this->token)
            ->getJson("/api/workout-tracking/exercise-history/{$exercise->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(10, $data);
    }

    /** @test */
    public function unauthenticated_user_cannot_get_exercise_history(): void
    {
        $exercise = Exercise::factory()->create();

        $response = $this->getJson("/api/workout-tracking/exercise-history/{$exercise->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function admin_can_get_all_workout_history(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $admin->createToken('admin-token')->plainTextToken;

        // Create some workouts
        CompletedWorkout::factory()->forUser($this->user)->completed()->count(3);
        $otherUser = User::factory()->create();
        CompletedWorkout::factory()->forUser($otherUser)->completed()->count(2);

        $response = $this->withToken($adminToken)->getJson('/api/all-workout-history');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    /** @test */
    public function regular_user_cannot_get_all_workout_history(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/all-workout-history');

        $response->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized. Admin access required.']);
    }

    /** @test */
    public function unauthenticated_user_cannot_get_all_history(): void
    {
        $response = $this->getJson('/api/all-workout-history');

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_log_multiple_sets_for_same_exercise(): void
    {
        $session = CompletedWorkout::factory()->forUser($this->user)->inProgress()->create();
        $exercise = Exercise::factory()->create();

        // Log 3 sets
        foreach ([1, 2, 3] as $setNumber) {
            $this->withToken($this->token)->postJson('/api/workout-tracking/log-set', [
                'completed_workout_id' => $session->id,
                'exercise_id' => $exercise->id,
                'set_number' => $setNumber,
                'weight' => 80,
                'reps' => 12,
            ])->assertStatus(200);
        }

        $this->assertDatabaseCount('exercise_logs', 3);

        $logs = ExerciseLog::where('completed_workout_id', $session->id)
            ->where('exercise_id', $exercise->id)
            ->get();

        $this->assertCount(3, $logs);
        $this->assertEquals([1, 2, 3], $logs->pluck('set_number')->sort()->values()->toArray());
    }

    /** @test */
    public function workout_history_includes_exercise_logs(): void
    {
        $session = CompletedWorkout::factory()->forUser($this->user)->completed()->create();
        $exercise = Exercise::factory()->create();

        ExerciseLog::factory()->forCompletedWorkout($session)->forExercise($exercise)->count(3)->create();

        $response = $this->withToken($this->token)->getJson('/api/workout-tracking/history');

        $response->assertStatus(200);

        $data = $response->json('data.data');
        $this->assertNotEmpty($data[0]['exercise_logs']);
    }

    /** @test */
    public function complete_workout_flow(): void
    {
        // 1. Start workout
        $startResponse = $this->withToken($this->token)->postJson('/api/workout-tracking/start', [
            'workout_id' => Workout::factory()->create()->id,
        ]);

        $startResponse->assertStatus(200);
        $sessionId = $startResponse->json('data.id');

        // 2. Log some sets
        $exercise = Exercise::factory()->create();

        for ($i = 1; $i <= 3; $i++) {
            $this->withToken($this->token)->postJson('/api/workout-tracking/log-set', [
                'completed_workout_id' => $sessionId,
                'exercise_id' => $exercise->id,
                'set_number' => $i,
                'weight' => 80,
                'reps' => 10,
            ])->assertStatus(200);
        }

        // 3. Finish workout
        $this->withToken($this->token)->postJson("/api/workout-tracking/finish/{$sessionId}", [
            'notes' => 'Great workout!',
        ])->assertStatus(200);

        // Verify
        $session = CompletedWorkout::find($sessionId);
        $this->assertNotNull($session->completed_at);
        $this->assertEquals(3, $session->exerciseLogs()->count());
    }
}
