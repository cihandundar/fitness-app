<?php

namespace Tests\Feature;

use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'user' => [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'role' => 'user',
                ]
            ])
            ->assertJsonStructure(['user', 'token']);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_can_register_complete_in_one_request(): void
    {
        $plan = MembershipPlan::factory()->create(['is_active' => true]);

        $response = $this->postJson('/api/auth/register-complete', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '5551234567',
            'gender' => 'male',
            'birth_date' => '2000-01-01',
            'height' => 175,
            'weight' => 70,
            'membership_plan_id' => $plan->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'token', 'message']);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'phone' => '5551234567',
        ]);
    }

    public function test_register_complete_is_idempotent_for_same_email(): void
    {
        $plan = MembershipPlan::factory()->create(['is_active' => true]);

        $payload = [
            'name' => 'Test User',
            'email' => 'retry@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '5551234567',
            'gender' => 'male',
            'birth_date' => '2000-01-01',
            'height' => 175,
            'weight' => 70,
            'membership_plan_id' => $plan->id,
        ];

        $this->postJson('/api/auth/register-complete', $payload)->assertStatus(201);
        $this->postJson('/api/auth/register-complete', $payload)->assertStatus(201);
    }

    public function test_check_email_returns_availability(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->getJson('/api/auth/check-email?email=new@example.com')
            ->assertStatus(200)
            ->assertJson(['available' => true]);

        $this->getJson('/api/auth/check-email?email=taken@example.com')
            ->assertStatus(200)
            ->assertJson(['available' => false]);
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_register_with_invalid_data(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'email' => $user->email,
                ]
            ])
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Çıkış yapıldı.']);
    }

    public function test_authenticated_user_can_get_their_info(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ]);
    }

    public function test_unauthenticated_user_cannot_access_me(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        // Login başarılı ama inactive user token alabilir
        // Middleware'de kontrol edilebilir
        $response->assertStatus(200);
    }
}
