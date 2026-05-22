<?php

namespace Tests\Feature;

use App\Models\ProgressLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressTest extends TestCase
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
    public function authenticated_user_can_list_their_progress_logs(): void
    {
        ProgressLog::factory()->count(3)->forUser($this->user)->create();

        $response = $this->withToken($this->token)->getJson('/api/progress');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'weight', 'body_fat', 'notes', 'logged_at']
                ],
                'meta' => ['total', 'per_page', 'current_page', 'last_page']
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_only_sees_their_own_progress_logs(): void
    {
        // Other user's logs
        $otherUser = User::factory()->create();
        ProgressLog::factory()->count(5)->for($otherUser)->create();

        // This user's logs
        ProgressLog::factory()->count(2)->forUser($this->user)->create();

        $response = $this->withToken($this->token)->getJson('/api/progress');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function unauthenticated_user_cannot_list_progress_logs(): void
    {
        $response = $this->getJson('/api/progress');

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_create_progress_log_with_weight_only(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/progress', [
            'weight' => 75.5,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'İlerleme kaydı başarıyla oluşturuldu.',
                'data' => [
                    'weight' => 75.5,
                ]
            ]);

        $this->assertDatabaseHas('progress_logs', [
            'user_id' => $this->user->id,
            'weight' => 75.5,
        ]);
    }

    /** @test */
    public function user_can_create_progress_log_with_body_fat(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/progress', [
            'weight' => 75,
            'body_fat' => 15.5,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('progress_logs', [
            'user_id' => $this->user->id,
            'weight' => 75,
            'body_fat' => 15.5,
        ]);
    }

    /** @test */
    public function user_can_create_progress_log_with_notes(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/progress', [
            'weight' => 75,
            'notes' => 'Diyete başladım, enerjim yüksek',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('progress_logs', [
            'user_id' => $this->user->id,
            'notes' => 'Diyete başladım, enerjim yüksek',
        ]);
    }

    /** @test */
    public function user_can_create_progress_log_with_custom_date(): void
    {
        $customDate = '2024-01-15 10:00:00';

        $response = $this->withToken($this->token)->postJson('/api/progress', [
            'weight' => 75,
            'logged_at' => $customDate,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('progress_logs', [
            'user_id' => $this->user->id,
            'logged_at' => $customDate,
        ]);
    }

    /** @test */
    public function progress_log_can_be_created_with_only_body_fat(): void
    {
        // Weight is nullable, so we can create log with just body_fat
        $response = $this->withToken($this->token)->postJson('/api/progress', [
            'body_fat' => 15,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('progress_logs', [
            'user_id' => $this->user->id,
            'body_fat' => 15,
        ]);
    }

    /** @test */
    public function weight_must_be_numeric(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/progress', [
            'weight' => 'not-a-number',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['weight']);
    }

    /** @test */
    public function body_fat_must_be_numeric_when_provided(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/progress', [
            'weight' => 75,
            'body_fat' => 'high',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body_fat']);
    }

    /** @test */
    public function user_can_view_their_own_progress_log(): void
    {
        $log = ProgressLog::factory()->forUser($this->user)->create([
            'weight' => 75,
        ]);

        $response = $this->withToken($this->token)->getJson("/api/progress/{$log->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $log->id,
                    'weight' => 75,
                ]
            ]);
    }

    /** @test */
    public function user_cannot_view_other_users_progress_log(): void
    {
        $otherUser = User::factory()->create();
        $log = ProgressLog::factory()->for($otherUser)->create();

        $response = $this->withToken($this->token)->getJson("/api/progress/{$log->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Bu kayda erişim izniniz yok.']);
    }

    /** @test */
    public function user_can_update_their_progress_log(): void
    {
        $log = ProgressLog::factory()->forUser($this->user)->create([
            'weight' => 75,
            'body_fat' => 20,
        ]);

        $response = $this->withToken($this->token)->putJson("/api/progress/{$log->id}", [
            'weight' => 74,
            'body_fat' => 18,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'İlerleme kaydı başarıyla güncellendi.',
                'data' => [
                    'weight' => 74,
                    'body_fat' => 18,
                ]
            ]);

        $log->refresh();
        $this->assertEquals(74, $log->weight);
        $this->assertEquals(18, $log->body_fat);
    }

    /** @test */
    public function user_can_partially_update_progress_log(): void
    {
        $log = ProgressLog::factory()->forUser($this->user)->create([
            'weight' => 75,
            'body_fat' => 20,
            'notes' => 'Original notes',
        ]);

        $response = $this->withToken($this->token)->putJson("/api/progress/{$log->id}", [
            'weight' => 74,
        ]);

        $response->assertStatus(200);

        $log->refresh();
        $this->assertEquals(74, $log->weight);
        $this->assertEquals(20, $log->body_fat); // unchanged
        $this->assertEquals('Original notes', $log->notes); // unchanged
    }

    /** @test */
    public function user_cannot_update_other_users_progress_log(): void
    {
        $otherUser = User::factory()->create();
        $log = ProgressLog::factory()->for($otherUser)->create([
            'weight' => 75,
        ]);

        $response = $this->withToken($this->token)->putJson("/api/progress/{$log->id}", [
            'weight' => 74,
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Bu kaydı güncelleme izniniz yok.']);

        $log->refresh();
        $this->assertEquals(75, $log->weight);
    }

    /** @test */
    public function user_can_delete_their_progress_log(): void
    {
        $log = ProgressLog::factory()->forUser($this->user)->create();

        $response = $this->withToken($this->token)->deleteJson("/api/progress/{$log->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'İlerleme kaydı başarıyla silindi.']);

        $this->assertDatabaseMissing('progress_logs', [
            'id' => $log->id,
        ]);
    }

    /** @test */
    public function user_cannot_delete_other_users_progress_log(): void
    {
        $otherUser = User::factory()->create();
        $log = ProgressLog::factory()->for($otherUser)->create();

        $response = $this->withToken($this->token)->deleteJson("/api/progress/{$log->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Bu kaydı silme izniniz yok.']);

        $this->assertDatabaseHas('progress_logs', [
            'id' => $log->id,
        ]);
    }

    /** @test */
    public function user_can_get_progress_stats(): void
    {
        // Create progress logs with different weights
        ProgressLog::factory()->forUser($this->user)->create([
            'weight' => 80,
            'logged_at' => '2024-01-01',
        ]);
        ProgressLog::factory()->forUser($this->user)->create([
            'weight' => 78,
            'logged_at' => '2024-01-15',
        ]);
        ProgressLog::factory()->forUser($this->user)->create([
            'weight' => 76,
            'logged_at' => '2024-02-01',
        ]);

        $response = $this->withToken($this->token)->getJson('/api/progress/stats');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_logs', 3)
            ->assertJsonPath('data.latest_weight', 76)
            ->assertJsonPath('data.starting_weight', 80)
            ->assertJsonPath('data.weight_change', -4);
    }

    /** @test */
    public function stats_returns_null_when_no_logs_exist(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/progress/stats');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'total_logs' => 0,
                    'latest_weight' => null,
                    'starting_weight' => null,
                    'weight_change' => null,
                ]
            ]);
    }

    /** @test */
    public function user_can_filter_progress_logs_by_date_range(): void
    {
        ProgressLog::factory()->forUser($this->user)->createMany([
            ['weight' => 80, 'logged_at' => '2024-01-01'],
            ['weight' => 78, 'logged_at' => '2024-01-15'],
            ['weight' => 76, 'logged_at' => '2024-02-01'],
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/progress?from=2024-01-10&to=2024-01-20');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.weight', 78);
    }

    /** @test */
    public function user_can_paginate_progress_logs(): void
    {
        ProgressLog::factory()->count(25)->forUser($this->user)->create();

        $response = $this->withToken($this->token)->getJson('/api/progress?per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.last_page', 3);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_stats(): void
    {
        $response = $this->getJson('/api/progress/stats');

        $response->assertStatus(401);
    }
}
