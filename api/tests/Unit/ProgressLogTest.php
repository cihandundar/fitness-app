<?php

namespace Tests\Unit;

use App\Models\ProgressLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressLogTest extends TestCase
{
    use RefreshDatabase;

    // ==================== Basic Attributes ====================

    public function test_progress_log_has_correct_fillable_attributes(): void
    {
        $user = User::factory()->create();

        $log = ProgressLog::factory()->create([
            'user_id' => $user->id,
            'weight' => 75.5,
            'body_fat' => 15.5,
            'notes' => 'Good progress',
        ]);

        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals(75.5, $log->weight);
        $this->assertEquals(15.5, $log->body_fat);
        $this->assertEquals('Good progress', $log->notes);
    }

    public function test_progress_log_casts_weight_to_float(): void
    {
        $log = ProgressLog::factory()->create(['weight' => 80.5]);

        $this->assertIsFloat($log->weight);
        $this->assertEquals(80.5, $log->weight);
    }

    public function test_progress_log_casts_body_fat_to_float(): void
    {
        $log = ProgressLog::factory()->create(['body_fat' => 12.5]);

        $this->assertIsFloat($log->body_fat);
        $this->assertEquals(12.5, $log->body_fat);
    }

    public function test_progress_log_casts_logged_at_to_datetime(): void
    {
        $log = ProgressLog::factory()->create([
            'logged_at' => '2024-01-15 10:30:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $log->logged_at);
    }

    // ==================== Relationships ====================

    public function test_progress_log_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $log = ProgressLog::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $log->user);
        $this->assertEquals($user->id, $log->user->id);
    }

    public function test_user_can_have_multiple_progress_logs(): void
    {
        $user = User::factory()->create();

        ProgressLog::factory()->create(['user_id' => $user->id]);
        ProgressLog::factory()->create(['user_id' => $user->id]);
        ProgressLog::factory()->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->progressLogs);
    }

    // ==================== Optional Fields ====================

    public function test_progress_log_weight_is_optional(): void
    {
        $log = ProgressLog::factory()->create(['weight' => null]);

        $this->assertNull($log->weight);
    }

    public function test_progress_log_body_fat_is_optional(): void
    {
        $log = ProgressLog::factory()->create(['body_fat' => null]);

        $this->assertNull($log->body_fat);
    }

    public function test_progress_log_notes_is_optional(): void
    {
        $log = ProgressLog::factory()->create(['notes' => null]);

        $this->assertNull($log->notes);
    }

    // ==================== Scopes and Ordering ====================

    public function test_progress_logs_are_ordered_by_date(): void
    {
        $user = User::factory()->create();

        ProgressLog::factory()->create([
            'user_id' => $user->id,
            'logged_at' => now()->subDays(2),
        ]);

        ProgressLog::factory()->create([
            'user_id' => $user->id,
            'logged_at' => now(),
        ]);

        ProgressLog::factory()->create([
            'user_id' => $user->id,
            'logged_at' => now()->subDay(),
        ]);

        $logs = $user->progressLogs()->orderBy('logged_at', 'desc')->get();

        $this->assertTrue($logs->first()->logged_at->gt($logs->last()->logged_at));
    }

    public function test_can_get_progress_logs_after_date(): void
    {
        $user = User::factory()->create();
        $cutoffDate = now()->subDays(7);

        ProgressLog::factory()->create([
            'user_id' => $user->id,
            'logged_at' => now()->subDays(10),
        ]);

        $recentLog = ProgressLog::factory()->create([
            'user_id' => $user->id,
            'logged_at' => now()->subDays(3),
        ]);

        $logs = $user->progressLogs()->where('logged_at', '>=', $cutoffDate)->get();

        $this->assertCount(1, $logs);
        $this->assertEquals($recentLog->id, $logs->first()->id);
    }

    // ==================== Progress Calculation ====================

    public function test_can_calculate_weight_difference(): void
    {
        $user = User::factory()->create();

        $firstLog = ProgressLog::factory()->create([
            'user_id' => $user->id,
            'weight' => 80,
            'logged_at' => now()->subDays(30),
        ]);

        $secondLog = ProgressLog::factory()->create([
            'user_id' => $user->id,
            'weight' => 75,
            'logged_at' => now(),
        ]);

        $difference = $firstLog->weight - $secondLog->weight;
        $this->assertEquals(5, $difference);
    }

    public function test_can_calculate_body_fat_difference(): void
    {
        $user = User::factory()->create();

        $firstLog = ProgressLog::factory()->create([
            'user_id' => $user->id,
            'body_fat' => 20,
            'logged_at' => now()->subDays(30),
        ]);

        $secondLog = ProgressLog::factory()->create([
            'user_id' => $user->id,
            'body_fat' => 18,
            'logged_at' => now(),
        ]);

        $difference = $firstLog->body_fat - $secondLog->body_fat;
        $this->assertEquals(2, $difference);
    }
}
