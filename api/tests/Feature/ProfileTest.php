<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'height' => null,
            'weight' => null,
        ]);
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function authenticated_user_can_view_their_profile(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $this->user->id,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_update_their_name(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/profile', [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Name',
                ],
                'message' => 'Profil başarıyla güncellendi.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function user_can_update_their_email(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/profile', [
            'email' => 'newemail@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'email' => 'newemail@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => 'newemail@example.com',
        ]);
    }

    /** @test */
    public function user_cannot_update_email_to_existing_one(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->withToken($this->token)->putJson('/api/profile', [
            'email' => 'existing@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function user_can_update_height(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/profile', [
            'height' => 180,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'height' => 180,
        ]);
    }

    /** @test */
    public function user_can_update_weight(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/profile', [
            'weight' => 75,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'weight' => 75,
        ]);
    }

    /** @test */
    public function height_must_be_at_least_50_cm(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/profile', [
            'height' => 49,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['height']);
    }

    /** @test */
    public function height_must_be_at_most_300_cm(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/profile', [
            'height' => 301,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['height']);
    }

    /** @test */
    public function weight_must_be_at_least_20_kg(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/profile', [
            'weight' => 19,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['weight']);
    }

    /** @test */
    public function weight_must_be_at_most_300_kg(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/profile', [
            'weight' => 301,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['weight']);
    }

    /** @test */
    public function user_can_update_birth_date(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/profile', [
            'birth_date' => '1995-05-15',
        ]);

        $response->assertStatus(200);

        // Verify the update - refresh from database
        $this->user->refresh();
        $this->assertEquals('1995-05-15', $this->user->birth_date->format('Y-m-d'));
    }

    /** @test */
    public function user_can_update_avatar(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/profile', [
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'avatar' => 'https://example.com/avatar.jpg',
        ]);
    }

    /** @test */
    public function user_can_update_password_with_correct_current_password(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/profile/password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Şifre başarıyla güncellendi.']);
    }

    /** @test */
    public function user_cannot_update_password_with_wrong_current_password(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/profile/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Mevcut şifre hatalı.']);
    }

    /** @test */
    public function password_update_requires_confirmation(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/profile/password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function password_must_be_at_least_8_characters(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/profile/password', [
            'current_password' => 'password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function user_can_delete_their_account(): void
    {
        $response = $this->withToken($this->token)->deleteJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Hesap başarıyla silindi.']);

        $this->assertDatabaseMissing('users', [
            'id' => $this->user->id,
        ]);

        // Token should be revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_delete_account(): void
    {
        $response = $this->deleteJson('/api/profile');

        $response->assertStatus(401);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
        ]);
    }

    /** @test */
    public function partial_update_only_updates_provided_fields(): void
    {
        $originalName = $this->user->name;
        $originalEmail = $this->user->email;

        $response = $this->withToken($this->token)->putJson('/api/profile', [
            'height' => 185,
        ]);

        $response->assertStatus(200);

        $this->user->refresh();
        $this->assertEquals($originalName, $this->user->name);
        $this->assertEquals($originalEmail, $this->user->email);
        $this->assertEquals(185, $this->user->height);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_profile(): void
    {
        $response = $this->putJson('/api/profile', [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(401);

        $this->assertDatabaseMissing('users', [
            'id' => $this->user->id,
            'name' => 'Hacked Name',
        ]);
    }

    /** @test */
    public function user_can_update_multiple_fields_at_once(): void
    {
        $response = $this->withToken($this->token)->putJson('/api/profile', [
            'name' => 'Updated Name',
            'height' => 180,
            'weight' => 75,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'height' => 180,
            'weight' => 75,
        ]);
    }
}
