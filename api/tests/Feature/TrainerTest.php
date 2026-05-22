<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\ProgressLog;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainerTest extends TestCase
{
    use RefreshDatabase;

    private User $trainer;
    private User $admin;
    private User $regularUser;
    private string $trainerToken;
    private string $adminToken;
    private string $userToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trainer = User::factory()->trainer()->create();
        $this->admin = User::factory()->admin()->create();
        $this->regularUser = User::factory()->create();

        $this->trainerToken = $this->trainer->createToken('test-token')->plainTextToken;
        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;
        $this->userToken = $this->regularUser->createToken('test-token')->plainTextToken;
    }

    // ==================== GET /trainer/clients Tests ====================

    public function test_trainer_can_list_their_clients(): void
    {
        $client1 = User::factory()->create();
        $client2 = User::factory()->create();

        $this->trainer->clients()->attach($client1, [
            'status' => 'active',
            'started_at' => now(),
            'total_days' => 30,
            'remaining_days' => 30,
            'last_check_in' => now(),
        ]);

        $this->trainer->clients()->attach($client2, [
            'status' => 'active',
            'started_at' => now(),
            'total_days' => 60,
            'remaining_days' => 60,
            'last_check_in' => now(),
        ]);

        $response = $this->withToken($this->trainerToken)->getJson('/api/trainer/clients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'name', 'email', 'pivot']
                ]
            ])
            ->assertJsonPath('status', 'success');

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_trainer_gets_empty_list_when_no_clients(): void
    {
        $response = $this->withToken($this->trainerToken)->getJson('/api/trainer/clients');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => []
            ]);
    }

    public function test_admin_can_list_their_clients(): void
    {
        $client1 = User::factory()->create();

        $this->admin->clients()->attach($client1, [
            'status' => 'active',
            'started_at' => now(),
            'total_days' => 30,
            'remaining_days' => 30,
            'last_check_in' => now(),
        ]);

        $response = $this->withToken($this->adminToken)->getJson('/api/trainer/clients');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    public function test_regular_user_cannot_list_clients(): void
    {
        $response = $this->withToken($this->userToken)->getJson('/api/trainer/clients');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_list_clients(): void
    {
        $response = $this->getJson('/api/trainer/clients');

        $response->assertStatus(401);
    }

    // ==================== POST /trainer/add-client Tests ====================

    public function test_trainer_can_add_new_client(): void
    {
        $client = User::factory()->create();

        $response = $this->withToken($this->trainerToken)->postJson('/api/trainer/add-client', [
            'email' => $client->email,
            'total_days' => 30,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Öğrenci başarıyla eklendi.'
            ]);

        $this->assertDatabaseHas('trainer_clients', [
            'trainer_id' => $this->trainer->id,
            'client_id' => $client->id,
            'status' => 'active',
            'total_days' => 30,
            'remaining_days' => 30,
        ]);
    }

    public function test_admin_can_add_new_client(): void
    {
        $client = User::factory()->create();

        $response = $this->withToken($this->adminToken)->postJson('/api/trainer/add-client', [
            'email' => $client->email,
            'total_days' => 45,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('trainer_clients', [
            'trainer_id' => $this->admin->id,
            'client_id' => $client->id,
            'total_days' => 45,
        ]);
    }

    public function test_trainer_cannot_add_same_client_twice(): void
    {
        $client = User::factory()->create();

        $this->trainer->clients()->attach($client, [
            'status' => 'active',
            'started_at' => now(),
            'total_days' => 30,
            'remaining_days' => 30,
            'last_check_in' => now(),
        ]);

        $response = $this->withToken($this->trainerToken)->postJson('/api/trainer/add-client', [
            'email' => $client->email,
            'total_days' => 30,
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Bu kullanıcı zaten sizin öğrenciniz.']);
    }

    public function test_regular_user_cannot_add_client(): void
    {
        $client = User::factory()->create();

        $response = $this->withToken($this->userToken)->postJson('/api/trainer/add-client', [
            'email' => $client->email,
            'total_days' => 30,
        ]);

        $response->assertStatus(403);
    }

    public function test_add_client_requires_email(): void
    {
        $response = $this->withToken($this->trainerToken)->postJson('/api/trainer/add-client', [
            'total_days' => 30,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_add_client_requires_valid_email(): void
    {
        $response = $this->withToken($this->trainerToken)->postJson('/api/trainer/add-client', [
            'email' => 'invalid-email',
            'total_days' => 30,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_add_client_requires_existing_user_email(): void
    {
        $response = $this->withToken($this->trainerToken)->postJson('/api/trainer/add-client', [
            'email' => 'nonexistent@example.com',
            'total_days' => 30,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_add_client_requires_total_days(): void
    {
        $client = User::factory()->create();

        $response = $this->withToken($this->trainerToken)->postJson('/api/trainer/add-client', [
            'email' => $client->email,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total_days']);
    }

    public function test_add_client_total_days_must_be_positive_integer(): void
    {
        $client = User::factory()->create();

        $response = $this->withToken($this->trainerToken)->postJson('/api/trainer/add-client', [
            'email' => $client->email,
            'total_days' => -5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total_days']);
    }

    // ==================== POST /trainer/assign-program Tests ====================

    public function test_trainer_can_assign_program_to_their_client(): void
    {
        $client = User::factory()->create();
        $program = Program::factory()->create();

        $this->trainer->clients()->attach($client, [
            'status' => 'active',
            'started_at' => now(),
            'total_days' => 30,
            'remaining_days' => 30,
            'last_check_in' => now(),
        ]);

        $response = $this->withToken($this->trainerToken)->postJson('/api/trainer/assign-program', [
            'client_id' => $client->id,
            'program_id' => $program->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Program başarıyla atandı.'
            ]);

        $this->assertDatabaseHas('user_programs', [
            'user_id' => $client->id,
            'program_id' => $program->id,
            'assigned_by' => $this->trainer->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_assign_program_to_any_client(): void
    {
        $trainer = User::factory()->trainer()->create();
        $client = User::factory()->create();
        $program = Program::factory()->create();

        $trainer->clients()->attach($client, [
            'status' => 'active',
            'started_at' => now(),
            'total_days' => 30,
            'remaining_days' => 30,
            'last_check_in' => now(),
        ]);

        $response = $this->withToken($this->adminToken)->postJson('/api/trainer/assign-program', [
            'client_id' => $client->id,
            'program_id' => $program->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_trainer_cannot_assign_program_to_non_client(): void
    {
        $otherUser = User::factory()->create();
        $program = Program::factory()->create();

        $response = $this->withToken($this->trainerToken)->postJson('/api/trainer/assign-program', [
            'client_id' => $otherUser->id,
            'program_id' => $program->id,
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Bu kullanıcı sizin öğrenciniz değil.']);
    }

    public function test_regular_user_cannot_assign_program(): void
    {
        $client = User::factory()->create();
        $program = Program::factory()->create();

        $response = $this->withToken($this->userToken)->postJson('/api/trainer/assign-program', [
            'client_id' => $client->id,
            'program_id' => $program->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_assign_program_requires_client_id(): void
    {
        $program = Program::factory()->create();

        $response = $this->withToken($this->trainerToken)->postJson('/api/trainer/assign-program', [
            'program_id' => $program->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client_id']);
    }

    public function test_assign_program_requires_program_id(): void
    {
        $client = User::factory()->create();

        $response = $this->withToken($this->trainerToken)->postJson('/api/trainer/assign-program', [
            'client_id' => $client->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['program_id']);
    }

    public function test_assign_program_requires_existing_client(): void
    {
        $program = Program::factory()->create();

        $response = $this->withToken($this->trainerToken)->postJson('/api/trainer/assign-program', [
            'client_id' => 99999,
            'program_id' => $program->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client_id']);
    }

    public function test_assign_program_requires_existing_program(): void
    {
        $client = User::factory()->create();

        $response = $this->withToken($this->trainerToken)->postJson('/api/trainer/assign-program', [
            'client_id' => $client->id,
            'program_id' => 99999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['program_id']);
    }

    // ==================== GET /trainer/client-progress/{id} Tests ====================

    public function test_trainer_can_view_their_client_progress(): void
    {
        $client = User::factory()->create();

        $this->trainer->clients()->attach($client, [
            'status' => 'active',
            'started_at' => now(),
            'total_days' => 30,
            'remaining_days' => 30,
            'last_check_in' => now(),
        ]);

        ProgressLog::factory()->create([
            'user_id' => $client->id,
            'weight' => 75.5,
            'body_fat' => 15.5,
        ]);

        $response = $this->withToken($this->trainerToken)->getJson("/api/trainer/client-progress/{$client->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'client' => ['id', 'name', 'email'],
                    'logs',
                    'workouts'
                ]
            ])
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.client.id', $client->id)
            ->assertJsonPath('data.client.name', $client->name)
            ->assertJsonPath('data.client.email', $client->email);
    }

    public function test_admin_can_view_any_client_progress(): void
    {
        $trainer = User::factory()->trainer()->create();
        $client = User::factory()->create();

        $trainer->clients()->attach($client, [
            'status' => 'active',
            'started_at' => now(),
            'total_days' => 30,
            'remaining_days' => 30,
            'last_check_in' => now(),
        ]);

        ProgressLog::factory()->create([
            'user_id' => $client->id,
        ]);

        $response = $this->withToken($this->adminToken)->getJson("/api/trainer/client-progress/{$client->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_trainer_cannot_view_non_client_progress(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->withToken($this->trainerToken)->getJson("/api/trainer/client-progress/{$otherUser->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Bu kullanıcının verilerini görme yetkiniz yok.']);
    }

    public function test_regular_user_cannot_view_client_progress(): void
    {
        $client = User::factory()->create();

        $response = $this->withToken($this->userToken)->getJson("/api/trainer/client-progress/{$client->id}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_view_client_progress(): void
    {
        $client = User::factory()->create();

        $response = $this->getJson("/api/trainer/client-progress/{$client->id}");

        $response->assertStatus(401);
    }

    public function test_client_progress_returns_403_for_nonexistent_user(): void
    {
        // Trainer yetkisi var ama user yoksa authorize kontrolü findOrFail'den önce çalışır
        // Bu nedenle 403 döner (kullanıcının bu datayı görme yetkisi yok)
        $response = $this->withToken($this->trainerToken)->getJson('/api/trainer/client-progress/99999');

        $response->assertStatus(403);
    }
}
