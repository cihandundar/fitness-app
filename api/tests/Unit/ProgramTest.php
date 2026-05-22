<?php

namespace Tests\Unit;

use App\Models\Program;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramTest extends TestCase
{
    use RefreshDatabase;

    // ==================== Basic Attributes ====================

    public function test_program_has_correct_fillable_attributes(): void
    {
        $program = Program::factory()->create([
            'title' => 'Test Program',
            'slug' => 'test-program',
            'description' => 'Test Description',
            'level' => 'beginner',
            'duration_weeks' => 8,
        ]);

        $this->assertEquals('Test Program', $program->title);
        $this->assertEquals('test-program', $program->slug);
        $this->assertEquals('beginner', $program->level);
        $this->assertEquals(8, $program->duration_weeks);
    }

    public function test_program_casts_boolean_fields(): void
    {
        $program = Program::factory()->create([
            'is_active' => true,
            'is_featured' => false,
            'is_custom' => true,
        ]);

        $this->assertIsBool($program->is_active);
        $this->assertIsBool($program->is_featured);
        $this->assertIsBool($program->is_custom);
        $this->assertTrue($program->is_active);
        $this->assertFalse($program->is_featured);
        $this->assertTrue($program->is_custom);
    }

    public function test_program_casts_settings_to_array(): void
    {
        $settings = ['difficulty' => 'advanced', 'goals' => ['strength', 'muscle']];
        $program = Program::factory()->create([
            'settings' => $settings,
        ]);

        $this->assertIsArray($program->settings);
        $this->assertEquals($settings, $program->settings);
    }

    // ==================== Relationships ====================

    public function test_program_belongs_to_creator(): void
    {
        $admin = User::factory()->admin()->create();
        $program = Program::factory()->create(['created_by' => $admin->id]);

        $this->assertInstanceOf(User::class, $program->creator);
        $this->assertEquals($admin->id, $program->creator->id);
    }

    public function test_program_can_have_target_user(): void
    {
        $targetUser = User::factory()->create();
        $program = Program::factory()->create([
            'is_custom' => true,
            'target_user_id' => $targetUser->id,
        ]);

        $this->assertInstanceOf(User::class, $program->targetUser);
        $this->assertEquals($targetUser->id, $program->targetUser->id);
    }

    public function test_program_can_have_many_users(): void
    {
        $program = Program::factory()->create();
        $trainer = User::factory()->trainer()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $program->users()->attach([$user1->id, $user2->id], [
            'assigned_by' => $trainer->id,
            'is_active' => true,
            'started_at' => now(),
        ]);

        $this->assertCount(2, $program->users);
        $this->assertTrue($program->users->contains($user1));
        $this->assertTrue($program->users->contains($user2));
    }

    public function test_program_user_pivot_includes_assignment_info(): void
    {
        $program = Program::factory()->create();
        $trainer = User::factory()->trainer()->create();
        $user = User::factory()->create();

        $program->users()->attach($user->id, [
            'assigned_by' => $trainer->id,
            'is_active' => true,
            'started_at' => now(),
        ]);

        $pivot = $program->users()->first()->pivot;
        $this->assertEquals($trainer->id, $pivot->assigned_by);
        $this->assertEquals(1, $pivot->is_active);
        $this->assertNotNull($pivot->started_at);
    }

    public function test_program_can_have_many_workouts(): void
    {
        $program = Program::factory()->create();

        Workout::factory()->create(['program_id' => $program->id]);
        Workout::factory()->create(['program_id' => $program->id]);
        Workout::factory()->create(['program_id' => $program->id]);

        $this->assertCount(3, $program->workouts);
        $this->assertInstanceOf(Workout::class, $program->workouts->first());
    }

    // ==================== Scopes and Queries ====================

    public function test_program_can_filter_by_level(): void
    {
        Program::factory()->create(['level' => 'beginner']);
        Program::factory()->create(['level' => 'advanced']);

        $beginnerPrograms = Program::where('level', 'beginner')->get();
        $advancedPrograms = Program::where('level', 'advanced')->get();

        $this->assertCount(1, $beginnerPrograms);
        $this->assertCount(1, $advancedPrograms);
    }

    public function test_program_can_filter_by_active_status(): void
    {
        Program::factory()->create(['is_active' => true]);
        Program::factory()->create(['is_active' => false]);

        $activePrograms = Program::where('is_active', true)->get();
        $inactivePrograms = Program::where('is_active', false)->get();

        $this->assertCount(1, $activePrograms);
        $this->assertCount(1, $inactivePrograms);
    }

    public function test_program_can_filter_by_featured(): void
    {
        Program::factory()->create(['is_featured' => true]);
        Program::factory()->create(['is_featured' => false]);

        $featuredPrograms = Program::where('is_featured', true)->get();

        $this->assertCount(1, $featuredPrograms);
    }

    // ==================== Custom Programs ====================

    public function test_custom_program_has_target_user(): void
    {
        $user = User::factory()->create();
        $customProgram = Program::factory()->create([
            'is_custom' => true,
            'target_user_id' => $user->id,
        ]);

        $this->assertTrue($customProgram->is_custom);
        $this->assertEquals($user->id, $customProgram->target_user_id);
    }

    public function test_non_custom_program_does_not_have_target_user(): void
    {
        $program = Program::factory()->create([
            'is_custom' => false,
            'target_user_id' => null,
        ]);

        $this->assertFalse($program->is_custom);
        $this->assertNull($program->target_user_id);
    }
}
