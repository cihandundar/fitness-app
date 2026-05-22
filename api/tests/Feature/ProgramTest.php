<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_anyone_can_list_programs(): void
    {
        Program::factory()->count(3)->create();

        $response = $this->getJson('/api/programs');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_anyone_can_view_single_program(): void
    {
        $program = Program::factory()->create();

        $response = $this->getJson("/api/programs/{$program->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $program->id,
                    'title' => $program->title,
                ]
            ]);
    }

    public function test_admin_can_create_program(): void
    {
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/programs', [
            'title' => 'Test Program',
            'level' => 'beginner',
            'duration_weeks' => 8,
            'description' => 'A test program',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'Test Program',
                    'level' => 'beginner',
                ]
            ]);

        $this->assertDatabaseHas('programs', [
            'title' => 'Test Program',
        ]);
    }

    public function test_non_admin_cannot_create_program(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/programs', [
            'title' => 'Test Program',
            'level' => 'beginner',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_program(): void
    {
        $program = Program::factory()->create();
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->putJson("/api/programs/{$program->id}", [
            'title' => 'Updated Program',
            'level' => 'advanced',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'Updated Program',
                    'level' => 'advanced',
                ]
            ]);
    }

    public function test_admin_can_delete_program(): void
    {
        $program = Program::factory()->create();
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->deleteJson("/api/programs/{$program->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('programs', [
            'id' => $program->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_create_program(): void
    {
        $response = $this->postJson('/api/programs', [
            'title' => 'Test Program',
        ]);

        $response->assertStatus(401);
    }

    public function test_program_filters_by_level(): void
    {
        Program::factory()->beginner()->count(2)->create();
        Program::factory()->advanced()->count(3)->create();

        $response = $this->getJson('/api/programs?level=beginner');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
