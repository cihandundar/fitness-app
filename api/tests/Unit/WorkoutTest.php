<?php

namespace Tests\Unit;

use App\Models\Workout;
use App\Models\Program;
use App\Models\Exercise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutTest extends TestCase
{
    use RefreshDatabase;

    // ==================== Basic Attributes ====================

    public function test_workout_has_correct_fillable_attributes(): void
    {
        $workout = Workout::factory()->create([
            'title' => 'Test Workout',
            'description' => 'Test Description',
            'day_number' => 1,
            'duration_minutes' => 45,
        ]);

        $this->assertEquals('Test Workout', $workout->title);
        $this->assertEquals('Test Description', $workout->description);
        $this->assertEquals(1, $workout->day_number);
        $this->assertEquals(45, $workout->duration_minutes);
    }

    // ==================== Relationships ====================

    public function test_workout_belongs_to_program(): void
    {
        $program = Program::factory()->create();
        $workout = Workout::factory()->create(['program_id' => $program->id]);

        $this->assertInstanceOf(Program::class, $workout->program);
        $this->assertEquals($program->id, $workout->program->id);
    }

    public function test_workout_can_have_many_exercises(): void
    {
        $workout = Workout::factory()->create();
        $exercise1 = Exercise::factory()->create();
        $exercise2 = Exercise::factory()->create();

        $workout->exercises()->attach($exercise1->id, [
            'sets' => 3,
            'reps' => 12,
            'rest_seconds' => 60,
            'order' => 1,
        ]);

        $workout->exercises()->attach($exercise2->id, [
            'sets' => 4,
            'reps' => 10,
            'rest_seconds' => 90,
            'order' => 2,
        ]);

        $this->assertCount(2, $workout->exercises);
        $this->assertTrue($workout->exercises->contains($exercise1));
        $this->assertTrue($workout->exercises->contains($exercise2));
    }

    public function test_workout_exercise_pivot_includes_exercise_details(): void
    {
        $workout = Workout::factory()->create();
        $exercise = Exercise::factory()->create();

        $workout->exercises()->attach($exercise->id, [
            'sets' => 4,
            'reps' => 10,
            'rest_seconds' => 90,
            'order' => 1,
        ]);

        $pivot = $workout->exercises()->first()->pivot;
        $this->assertEquals(4, $pivot->sets);
        $this->assertEquals(10, $pivot->reps);
        $this->assertEquals(90, $pivot->rest_seconds);
        $this->assertEquals(1, $pivot->order);
    }

    // ==================== Day Numbers ====================

    public function test_workout_day_number_must_be_valid(): void
    {
        $program = Program::factory()->create();

        $workout1 = Workout::factory()->create([
            'program_id' => $program->id,
            'day_number' => 1,
        ]);

        $workout2 = Workout::factory()->create([
            'program_id' => $program->id,
            'day_number' => 7,
        ]);

        $this->assertEquals(1, $workout1->day_number);
        $this->assertEquals(7, $workout2->day_number);
    }

    public function test_program_workouts_can_be_ordered_by_day(): void
    {
        $program = Program::factory()->create();

        Workout::factory()->create([
            'program_id' => $program->id,
            'day_number' => 3,
        ]);

        Workout::factory()->create([
            'program_id' => $program->id,
            'day_number' => 1,
        ]);

        Workout::factory()->create([
            'program_id' => $program->id,
            'day_number' => 2,
        ]);

        $workouts = $program->workouts()->orderBy('day_number')->get();

        $this->assertEquals(1, $workouts->get(0)->day_number);
        $this->assertEquals(2, $workouts->get(1)->day_number);
        $this->assertEquals(3, $workouts->get(2)->day_number);
    }

    // ==================== Duration ====================

    public function test_workout_can_have_duration(): void
    {
        $workout = Workout::factory()->create(['duration_minutes' => 60]);
        $this->assertEquals(60, $workout->duration_minutes);
    }

    // ==================== Exercises Count ====================

    public function test_workout_can_count_exercises(): void
    {
        $workout = Workout::factory()->create();
        $exercise1 = Exercise::factory()->create();
        $exercise2 = Exercise::factory()->create();
        $exercise3 = Exercise::factory()->create();

        $workout->exercises()->attach($exercise1->id, [
            'sets' => 3, 'reps' => 10, 'rest_seconds' => 60, 'order' => 1
        ]);
        $workout->exercises()->attach($exercise2->id, [
            'sets' => 3, 'reps' => 10, 'rest_seconds' => 60, 'order' => 2
        ]);
        $workout->exercises()->attach($exercise3->id, [
            'sets' => 3, 'reps' => 10, 'rest_seconds' => 60, 'order' => 3
        ]);

        $this->assertEquals(3, $workout->exercises()->count());
    }

    public function test_workout_with_no_exercises(): void
    {
        $workout = Workout::factory()->create();
        $this->assertEquals(0, $workout->exercises()->count());
    }

    // ==================== Pivot Order ====================

    public function test_exercises_are_ordered_by_pivot_order(): void
    {
        $workout = Workout::factory()->create();
        $exercise1 = Exercise::factory()->create();
        $exercise2 = Exercise::factory()->create();
        $exercise3 = Exercise::factory()->create();

        $workout->exercises()->attach($exercise1->id, [
            'sets' => 3, 'reps' => 10, 'rest_seconds' => 60, 'order' => 2
        ]);
        $workout->exercises()->attach($exercise2->id, [
            'sets' => 3, 'reps' => 10, 'rest_seconds' => 60, 'order' => 1
        ]);
        $workout->exercises()->attach($exercise3->id, [
            'sets' => 3, 'reps' => 10, 'rest_seconds' => 60, 'order' => 3
        ]);

        $exercises = $workout->exercises;

        $this->assertEquals($exercise2->id, $exercises->get(0)->id);
        $this->assertEquals($exercise1->id, $exercises->get(1)->id);
        $this->assertEquals($exercise3->id, $exercises->get(2)->id);
    }
}
