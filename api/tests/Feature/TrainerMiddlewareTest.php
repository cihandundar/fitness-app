<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\User as UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainerMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $superAdmin;
    private User $trainer;
    private User $regularUser;
    private string $adminToken;
    private string $superAdminToken;
    private string $trainerToken;
    private string $userToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->superAdmin = User::factory()->superAdmin()->create();
        $this->trainer = User::factory()->trainer()->create();
        $this->regularUser = User::factory()->create();

        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;
        $this->superAdminToken = $this->superAdmin->createToken('test-token')->plainTextToken;
        $this->trainerToken = $this->trainer->createToken('test-token')->plainTextToken;
        $this->userToken = $this->regularUser->createToken('test-token')->plainTextToken;
    }

    // ==================== Trainer Clients Endpoint Tests ====================

    public function test_trainer_can_list_clients(): void
    {
        $response = $this->withToken($this->trainerToken)->getJson('/api/trainer/clients');

        $response->assertStatus(200);
    }

    public function test_admin_can_list_clients(): void
    {
        $response = $this->withToken($this->adminToken)->getJson('/api/trainer/clients');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_list_clients(): void
    {
        $response = $this->withToken($this->superAdminToken)->getJson('/api/trainer/clients');

        $response->assertStatus(200);
    }

    public function test_regular_user_cannot_list_clients(): void
    {
        $response = $this->withToken($this->userToken)->getJson('/api/trainer/clients');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_list_clients(): void
    {
        $response = $this->getJson('/api/trainer/clients');

        $response->assertStatus(401);
    }

    // ==================== Trainer Add Client Endpoint Tests ====================

    public function test_trainer_can_add_client(): void
    {
        $client = UserModel::factory()->create();

        $response = $this->withToken($this->trainerToken)->postJson('/api/trainer/add-client', [
            'email' => $client->email,
            'total_days' => 30,
        ]);

        $response->assertStatus(200);
    }

    public function test_admin_can_add_client(): void
    {
        $client = UserModel::factory()->create();

        $response = $this->withToken($this->adminToken)->postJson('/api/trainer/add-client', [
            'email' => $client->email,
            'total_days' => 30,
        ]);

        $response->assertStatus(200);
    }

    public function test_super_admin_can_add_client(): void
    {
        $client = UserModel::factory()->create();

        $response = $this->withToken($this->superAdminToken)->postJson('/api/trainer/add-client', [
            'email' => $client->email,
            'total_days' => 30,
        ]);

        $response->assertStatus(200);
    }

    public function test_regular_user_cannot_add_client(): void
    {
        $client = UserModel::factory()->create();

        $response = $this->withToken($this->userToken)->postJson('/api/trainer/add-client', [
            'email' => $client->email,
            'total_days' => 30,
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_add_client(): void
    {
        $client = UserModel::factory()->create();

        $response = $this->postJson('/api/trainer/add-client', [
            'email' => $client->email,
            'total_days' => 30,
        ]);

        $response->assertStatus(401);
    }

    // ==================== Trainer Assign Program Endpoint Tests ====================

    public function test_trainer_can_assign_program(): void
    {
        $client = UserModel::factory()->create();
        $program = \App\Models\Program::factory()->create();

        // First add client
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

        $response->assertStatus(200);
    }

    public function test_admin_can_assign_program(): void
    {
        $trainer = User::factory()->trainer()->create();
        $client = UserModel::factory()->create();
        $program = \App\Models\Program::factory()->create();

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

        $response->assertStatus(200);
    }

    public function test_super_admin_can_assign_program(): void
    {
        $trainer = User::factory()->trainer()->create();
        $client = UserModel::factory()->create();
        $program = \App\Models\Program::factory()->create();

        $trainer->clients()->attach($client, [
            'status' => 'active',
            'started_at' => now(),
            'total_days' => 30,
            'remaining_days' => 30,
            'last_check_in' => now(),
        ]);

        $response = $this->withToken($this->superAdminToken)->postJson('/api/trainer/assign-program', [
            'client_id' => $client->id,
            'program_id' => $program->id,
        ]);

        $response->assertStatus(200);
    }

    public function test_regular_user_cannot_assign_program(): void
    {
        $client = UserModel::factory()->create();
        $program = \App\Models\Program::factory()->create();

        $response = $this->withToken($this->userToken)->postJson('/api/trainer/assign-program', [
            'client_id' => $client->id,
            'program_id' => $program->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_assign_program(): void
    {
        $client = UserModel::factory()->create();
        $program = \App\Models\Program::factory()->create();

        $response = $this->postJson('/api/trainer/assign-program', [
            'client_id' => $client->id,
            'program_id' => $program->id,
        ]);

        $response->assertStatus(401);
    }

    // ==================== Trainer Client Progress Endpoint Tests ====================

    public function test_trainer_can_view_client_progress(): void
    {
        $client = UserModel::factory()->create();

        $this->trainer->clients()->attach($client, [
            'status' => 'active',
            'started_at' => now(),
            'total_days' => 30,
            'remaining_days' => 30,
            'last_check_in' => now(),
        ]);

        $response = $this->withToken($this->trainerToken)->getJson("/api/trainer/client-progress/{$client->id}");

        $response->assertStatus(200);
    }

    public function test_admin_can_view_client_progress(): void
    {
        $trainer = User::factory()->trainer()->create();
        $client = UserModel::factory()->create();

        $trainer->clients()->attach($client, [
            'status' => 'active',
            'started_at' => now(),
            'total_days' => 30,
            'remaining_days' => 30,
            'last_check_in' => now(),
        ]);

        $response = $this->withToken($this->adminToken)->getJson("/api/trainer/client-progress/{$client->id}");

        $response->assertStatus(200);
    }

    public function test_super_admin_can_view_client_progress(): void
    {
        $trainer = User::factory()->trainer()->create();
        $client = UserModel::factory()->create();

        $trainer->clients()->attach($client, [
            'status' => 'active',
            'started_at' => now(),
            'total_days' => 30,
            'remaining_days' => 30,
            'last_check_in' => now(),
        ]);

        $response = $this->withToken($this->superAdminToken)->getJson("/api/trainer/client-progress/{$client->id}");

        $response->assertStatus(200);
    }

    public function test_regular_user_cannot_view_client_progress(): void
    {
        $client = UserModel::factory()->create();

        $response = $this->withToken($this->userToken)->getJson("/api/trainer/client-progress/{$client->id}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_view_client_progress(): void
    {
        $client = UserModel::factory()->create();

        $response = $this->getJson("/api/trainer/client-progress/{$client->id}");

        $response->assertStatus(401);
    }
}
