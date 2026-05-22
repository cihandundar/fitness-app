<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->superAdmin = User::factory()->superAdmin()->create();
    }

    public function test_admin_can_list_all_users(): void
    {
        User::factory()->count(10)->create();

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(12, 'data'); // 10 + admin + superAdmin
    }

    public function test_admin_can_view_single_user(): void
    {
        $user = User::factory()->create();
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ]
            ]);
    }

    public function test_admin_can_update_user_role(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->putJson("/api/users/{$user->id}", [
            'role' => 'trainer',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'role' => 'trainer',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'trainer',
        ]);
    }

    public function test_admin_can_toggle_user_active_status(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->putJson("/api/users/{$user->id}", [
            'is_active' => false,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create();
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_regular_user_cannot_access_user_management(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function test_trainer_can_view_their_own_clients(): void
    {
        $trainer = User::factory()->trainer()->create();
        $client = User::factory()->create();
        $trainerToken = $trainer->createToken('test-token')->plainTextToken;

        // Attach client to trainer
        $trainer->clients()->attach($client->id, [
            'status' => 'active',
            'started_at' => now(),
        ]);

        $response = $this->withToken($trainerToken)->getJson('/api/trainer/clients');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_users_can_be_filtered_by_role(): void
    {
        User::factory()->count(5)->create(['role' => 'user']);
        User::factory()->count(3)->trainer()->create();
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/users?role=trainer');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_unauthenticated_user_cannot_access_users(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }
}
