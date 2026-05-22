<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $trainer;
    private User $member;
    private string $adminToken;
    private string $trainerToken;
    private string $memberToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->trainer = User::factory()->trainer()->create();
        $this->member = User::factory()->create();

        $this->adminToken = $this->admin->createToken('admin-token')->plainTextToken;
        $this->trainerToken = $this->trainer->createToken('trainer-token')->plainTextToken;
        $this->memberToken = $this->member->createToken('member-token')->plainTextToken;
    }

    // ========== INDEX: LIST APPOINTMENTS ==========

    /** @test */
    public function member_can_list_their_own_appointments(): void
    {
        Appointment::factory()->create(['user_id' => $this->member->id, 'trainer_id' => $this->trainer->id]);
        Appointment::factory()->create(['user_id' => $this->member->id, 'trainer_id' => $this->trainer->id]);

        $otherMember = User::factory()->create();
        Appointment::factory()->create(['user_id' => $otherMember->id, 'trainer_id' => $this->trainer->id]); // Other user's appointment

        $response = $this->withToken($this->memberToken)->getJson('/api/appointments');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function trainer_can_list_their_appointments(): void
    {
        $otherTrainer = User::factory()->trainer()->create();

        Appointment::factory()->create(['user_id' => $this->member->id, 'trainer_id' => $this->trainer->id]);
        Appointment::factory()->create(['user_id' => $this->member->id, 'trainer_id' => $this->trainer->id]);
        Appointment::factory()->create(['user_id' => $this->member->id, 'trainer_id' => $otherTrainer->id]); // Other trainer's appointment

        $response = $this->withToken($this->trainerToken)->getJson('/api/appointments');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function appointments_are_ordered_by_start_time_asc(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
            'start_time' => now()->addDays(3),
            'end_time' => now()->addDays(3)->addHour(),
        ]);
        Appointment::factory()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHour(),
        ]);
        Appointment::factory()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
            'start_time' => now()->addDays(2),
            'end_time' => now()->addDays(2)->addHour(),
        ]);

        $response = $this->withToken($this->memberToken)->getJson('/api/appointments');

        $response->assertStatus(200);

        $startTimes = collect($response->json('data'))->pluck('start_time')->toArray();
        $this->assertTrue(
            Carbon::parse($startTimes[0])->lt(Carbon::parse($startTimes[1])) &&
            Carbon::parse($startTimes[1])->lt(Carbon::parse($startTimes[2]))
        );
    }

    /** @test */
    public function member_list_includes_trainer_info(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->memberToken)->getJson('/api/appointments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'trainer' => ['id', 'name'],
                        'start_time',
                        'end_time',
                        'status',
                    ]
                ]
            ]);
    }

    /** @test */
    public function trainer_list_includes_user_info(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->trainerToken)->getJson('/api/appointments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user' => ['id', 'name', 'email'],
                        'start_time',
                        'end_time',
                        'status',
                    ]
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_list_appointments(): void
    {
        $response = $this->getJson('/api/appointments');

        $response->assertStatus(401);
    }

    // ========== SHOW: VIEW SINGLE APPOINTMENT ==========

    /** @test */
    public function member_can_view_own_appointment(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->memberToken)->getJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $appointment->id,
                ]
            ]);
    }

    /** @test */
    public function trainer_can_view_appointment_with_them(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->trainerToken)->getJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_view_any_appointment(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->adminToken)->getJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function member_cannot_view_others_appointment(): void
    {
        $otherMember = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $otherMember->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->memberToken)->getJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_view_appointment(): void
    {
        $appointment = Appointment::factory()->create();

        $response = $this->getJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(401);
    }

    // ========== STORE: CREATE APPOINTMENT ==========

    /** @test */
    public function member_can_create_appointment(): void
    {
        $startTime = now()->addDays(2)->setTime(10, 0);
        $endTime = now()->addDays(2)->setTime(11, 0);

        $response = $this->withToken($this->memberToken)->postJson('/api/appointments', [
            'trainer_id' => $this->trainer->id,
            'start_time' => $startTime->toDateTimeString(),
            'end_time' => $endTime->toDateTimeString(),
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Randevu talebi oluşturuldu.',
            ]);

        $this->assertDatabaseHas('appointments', [
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function appointment_creation_requires_trainer_id(): void
    {
        $response = $this->withToken($this->memberToken)->postJson('/api/appointments', [
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHour(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['trainer_id']);
    }

    /** @test */
    public function appointment_creation_requires_start_time(): void
    {
        $response = $this->withToken($this->memberToken)->postJson('/api/appointments', [
            'trainer_id' => $this->trainer->id,
            'end_time' => now()->addDays(1),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_time']);
    }

    /** @test */
    public function appointment_creation_requires_end_time(): void
    {
        $response = $this->withToken($this->memberToken)->postJson('/api/appointments', [
            'trainer_id' => $this->trainer->id,
            'start_time' => now()->addDays(1),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_time']);
    }

    /** @test */
    public function start_time_must_be_in_future(): void
    {
        $response = $this->withToken($this->memberToken)->postJson('/api/appointments', [
            'trainer_id' => $this->trainer->id,
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_time']);
    }

    /** @test */
    public function end_time_must_be_after_start_time(): void
    {
        $startTime = now()->addDays(1)->setTime(10, 0);

        $response = $this->withToken($this->memberToken)->postJson('/api/appointments', [
            'trainer_id' => $this->trainer->id,
            'start_time' => $startTime->toDateTimeString(),
            'end_time' => $startTime->subHour()->toDateTimeString(), // Before start time
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_time']);
    }

    /** @test */
    public function trainer_id_must_exist(): void
    {
        $response = $this->withToken($this->memberToken)->postJson('/api/appointments', [
            'trainer_id' => 999,
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHour(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['trainer_id']);
    }

    /** @test */
    public function cannot_create_overlapping_appointment(): void
    {
        $startTime = now()->addDays(1)->setTime(10, 0);
        $endTime = now()->addDays(1)->setTime(11, 0);

        // Create existing appointment
        Appointment::factory()->create([
            'trainer_id' => $this->trainer->id,
            'user_id' => $this->member->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        // Try to create overlapping appointment
        $response = $this->withToken($this->memberToken)->postJson('/api/appointments', [
            'trainer_id' => $this->trainer->id,
            'start_time' => $startTime->addMinutes(30)->toDateTimeString(),
            'end_time' => $endTime->addMinutes(30)->toDateTimeString(),
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Bu saat diliminde eğitmenin başka bir randevusu bulunuyor.']);
    }

    /** @test */
    public function new_appointment_status_defaults_to_pending(): void
    {
        $response = $this->withToken($this->memberToken)->postJson('/api/appointments', [
            'trainer_id' => $this->trainer->id,
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHour(),
        ]);

        $response->assertStatus(201);

        $appointment = Appointment::first();
        $this->assertEquals('pending', $appointment->status);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_appointment(): void
    {
        $response = $this->postJson('/api/appointments', [
            'trainer_id' => $this->trainer->id,
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHour(),
        ]);

        $response->assertStatus(401);
    }

    // ========== UPDATE: CHANGE STATUS ==========

    /** @test */
    public function trainer_can_confirm_appointment(): void
    {
        $appointment = Appointment::factory()->pending()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->trainerToken)->putJson("/api/appointments/{$appointment->id}", [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Randevu durumu güncellendi.',
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'confirmed',
        ]);
    }

    /** @test */
    public function admin_can_confirm_appointment(): void
    {
        $appointment = Appointment::factory()->pending()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->adminToken)->putJson("/api/appointments/{$appointment->id}", [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function trainer_can_cancel_appointment(): void
    {
        $appointment = Appointment::factory()->confirmed()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->trainerToken)->putJson("/api/appointments/{$appointment->id}", [
            'status' => 'cancelled',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function member_cannot_confirm_appointment(): void
    {
        $appointment = Appointment::factory()->pending()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->memberToken)->putJson("/api/appointments/{$appointment->id}", [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function other_trainer_cannot_modify_appointment(): void
    {
        $otherTrainer = User::factory()->trainer()->create();
        $otherTrainerToken = $otherTrainer->createToken('trainer-token')->plainTextToken;

        $appointment = Appointment::factory()->pending()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($otherTrainerToken)->putJson("/api/appointments/{$appointment->id}", [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function status_must_be_valid(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->trainerToken)->putJson("/api/appointments/{$appointment->id}", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function status_is_required(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->trainerToken)->putJson("/api/appointments/{$appointment->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function all_valid_statuses_are_accepted(): void
    {
        $statuses = ['confirmed', 'cancelled', 'completed'];

        foreach ($statuses as $status) {
            $appointment = Appointment::factory()->pending()->create([
                'user_id' => $this->member->id,
                'trainer_id' => $this->trainer->id,
            ]);

            $response = $this->withToken($this->trainerToken)->putJson("/api/appointments/{$appointment->id}", [
                'status' => $status,
            ]);

            $response->assertStatus(200);

            $this->assertDatabaseHas('appointments', [
                'id' => $appointment->id,
                'status' => $status,
            ]);
        }
    }

    /** @test */
    public function unauthenticated_user_cannot_update_appointment(): void
    {
        $appointment = Appointment::factory()->create();

        $response = $this->putJson("/api/appointments/{$appointment->id}", [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(401);
    }

    // ========== DESTROY: DELETE APPOINTMENT ==========

    /** @test */
    public function member_can_delete_own_appointment(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->memberToken)->deleteJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Randevu iptal edildi.'
            ]);

        $this->assertDatabaseMissing('appointments', [
            'id' => $appointment->id,
        ]);
    }

    /** @test */
    public function trainer_can_delete_appointment(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->trainerToken)->deleteJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_delete_any_appointment(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->adminToken)->deleteJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function member_cannot_delete_others_appointment(): void
    {
        $otherMember = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $otherMember->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($this->memberToken)->deleteJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function other_trainer_cannot_delete_appointment(): void
    {
        $otherTrainer = User::factory()->trainer()->create();
        $otherTrainerToken = $otherTrainer->createToken('trainer-token')->plainTextToken;

        $appointment = Appointment::factory()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
        ]);

        $response = $this->withToken($otherTrainerToken)->deleteJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_delete_appointment(): void
    {
        $appointment = Appointment::factory()->create();

        $response = $this->deleteJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(401);
    }

    // ========== ADDITIONAL TESTS ==========

    /** @test */
    public function notes_are_optional_when_creating_appointment(): void
    {
        $response = $this->withToken($this->memberToken)->postJson('/api/appointments', [
            'trainer_id' => $this->trainer->id,
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHour(),
        ]);

        $response->assertStatus(201);

        $appointment = Appointment::first();
        $this->assertNull($appointment->notes);
    }

    /** @test */
    public function appointment_with_notes_can_be_created(): void
    {
        $response = $this->withToken($this->memberToken)->postJson('/api/appointments', [
            'trainer_id' => $this->trainer->id,
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHour(),
            'notes' => 'Bring water bottle and towel',
        ]);

        $response->assertStatus(201);

        $appointment = Appointment::first();
        $this->assertEquals('Bring water bottle and towel', $appointment->notes);
    }

    /** @test */
    public function appointment_detail_includes_all_fields(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
            'notes' => 'Test notes',
        ]);

        $response = $this->withToken($this->memberToken)->getJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'trainer',
                    'user',
                    'start_time',
                    'end_time',
                    'status',
                    'notes',
                    'created_at',
                ]
            ]);
    }

    /** @test */
    public function can_create_back_to_back_appointments_for_different_trainers(): void
    {
        $otherTrainer = User::factory()->trainer()->create();
        $startTime = now()->addDays(1)->setTime(10, 0);

        // First appointment with trainer 1
        $this->withToken($this->memberToken)->postJson('/api/appointments', [
            'trainer_id' => $this->trainer->id,
            'start_time' => $startTime->toDateTimeString(),
            'end_time' => $startTime->addHour()->toDateTimeString(),
        ])->assertStatus(201);

        // Second appointment with trainer 2 at same time - should be allowed
        $response = $this->withToken($this->memberToken)->postJson('/api/appointments', [
            'trainer_id' => $otherTrainer->id,
            'start_time' => $startTime->toDateTimeString(),
            'end_time' => $startTime->copy()->addHour()->toDateTimeString(),
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function can_create_appointment_after_existing_one_ends(): void
    {
        $startTime = now()->addDays(1)->setTime(10, 0);

        // First appointment
        Appointment::factory()->create([
            'trainer_id' => $this->trainer->id,
            'user_id' => $this->member->id,
            'start_time' => $startTime,
            'end_time' => $startTime->addHour(),
        ]);

        // Second appointment starting when first ends
        $response = $this->withToken($this->memberToken)->postJson('/api/appointments', [
            'trainer_id' => $this->trainer->id,
            'start_time' => $startTime->addHour()->toDateTimeString(),
            'end_time' => $startTime->addHours(2)->toDateTimeString(),
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function can_create_appointment_before_existing_one_starts(): void
    {
        $startTime = now()->addDays(1)->setTime(10, 0);

        // Existing appointment
        Appointment::factory()->create([
            'trainer_id' => $this->trainer->id,
            'user_id' => $this->member->id,
            'start_time' => $startTime,
            'end_time' => (clone $startTime)->addHour(),
        ]);

        // New appointment ending when existing starts
        $newStartTime = (clone $startTime)->subHour();
        $response = $this->withToken($this->memberToken)->postJson('/api/appointments', [
            'trainer_id' => $this->trainer->id,
            'start_time' => $newStartTime->toDateTimeString(),
            'end_time' => $startTime->toDateTimeString(),
        ]);

        $response->assertStatus(201);
    }
}
