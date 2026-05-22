<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    // ==================== Basic Attributes ====================

    public function test_appointment_has_correct_fillable_attributes(): void
    {
        $trainer = User::factory()->trainer()->create();
        $member = User::factory()->create();

        $appointment = Appointment::factory()->create([
            'trainer_id' => $trainer->id,
            'user_id' => $member->id,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
            'status' => 'scheduled',
            'notes' => 'First session',
        ]);

        $this->assertEquals($trainer->id, $appointment->trainer_id);
        $this->assertEquals($member->id, $appointment->user_id);
        $this->assertEquals('scheduled', $appointment->status);
        $this->assertEquals('First session', $appointment->notes);
    }

    public function test_appointment_casts_dates_to_datetime(): void
    {
        $appointment = Appointment::factory()->create([
            'start_time' => '2024-06-01 10:00:00',
            'end_time' => '2024-06-01 11:00:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $appointment->start_time);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $appointment->end_time);
    }

    // ==================== Relationships ====================

    public function test_appointment_belongs_to_trainer(): void
    {
        $trainer = User::factory()->trainer()->create();
        $appointment = Appointment::factory()->create(['trainer_id' => $trainer->id]);

        $this->assertInstanceOf(User::class, $appointment->trainer);
        $this->assertEquals($trainer->id, $appointment->trainer->id);
        $this->assertEquals('trainer', $appointment->trainer->role);
    }

    public function test_appointment_belongs_to_member(): void
    {
        $member = User::factory()->create();
        $appointment = Appointment::factory()->create(['user_id' => $member->id]);

        $this->assertInstanceOf(User::class, $appointment->user);
        $this->assertEquals($member->id, $appointment->user->id);
    }

    // ==================== Status Values ====================

    public function test_appointment_can_be_scheduled(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'scheduled']);
        $this->assertEquals('scheduled', $appointment->status);
    }

    public function test_appointment_can_be_confirmed(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'confirmed']);
        $this->assertEquals('confirmed', $appointment->status);
    }

    public function test_appointment_can_be_completed(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'completed']);
        $this->assertEquals('completed', $appointment->status);
    }

    public function test_appointment_can_be_cancelled(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'cancelled']);
        $this->assertEquals('cancelled', $appointment->status);
    }

    public function test_appointment_can_be_no_show(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'no_show']);
        $this->assertEquals('no_show', $appointment->status);
    }

    // ==================== Time Calculations ====================

    public function test_appointment_duration_calculation(): void
    {
        $appointment = Appointment::factory()->create([
            'start_time' => now()->setHour(10)->setMinute(0)->setSecond(0),
            'end_time' => now()->setHour(11)->setMinute(30)->setSecond(0),
        ]);

        $durationInMinutes = $appointment->start_time->diffInMinutes($appointment->end_time);
        $this->assertEquals(90, $durationInMinutes);
    }

    public function test_appointment_can_be_one_hour(): void
    {
        $appointment = Appointment::factory()->create([
            'start_time' => now()->setHour(14)->setMinute(0),
            'end_time' => now()->setHour(15)->setMinute(0),
        ]);

        $durationInMinutes = $appointment->start_time->diffInMinutes($appointment->end_time);
        $this->assertEquals(60, $durationInMinutes);
    }

    // ==================== Scopes ====================

    public function test_can_get_upcoming_appointments(): void
    {
        $trainer = User::factory()->trainer()->create();
        $member = User::factory()->create();

        Appointment::factory()->create([
            'trainer_id' => $trainer->id,
            'user_id' => $member->id,
            'start_time' => now()->subDay(),
            'status' => 'completed',
        ]);

        Appointment::factory()->create([
            'trainer_id' => $trainer->id,
            'user_id' => $member->id,
            'start_time' => now()->addDay(),
            'status' => 'scheduled',
        ]);

        $upcomingAppointments = Appointment::where('start_time', '>', now())
            ->where('status', 'scheduled')
            ->get();

        $this->assertCount(1, $upcomingAppointments);
    }

    public function test_can_get_past_appointments(): void
    {
        $trainer = User::factory()->trainer()->create();
        $member = User::factory()->create();

        Appointment::factory()->create([
            'trainer_id' => $trainer->id,
            'user_id' => $member->id,
            'start_time' => now()->subDays(2),
            'status' => 'completed',
        ]);

        Appointment::factory()->create([
            'trainer_id' => $trainer->id,
            'user_id' => $member->id,
            'start_time' => now()->addDay(),
            'status' => 'scheduled',
        ]);

        $pastAppointments = Appointment::where('start_time', '<', now())->get();

        $this->assertCount(1, $pastAppointments);
    }

    public function test_can_filter_by_status(): void
    {
        $trainer = User::factory()->trainer()->create();
        $member = User::factory()->create();

        Appointment::factory()->create([
            'trainer_id' => $trainer->id,
            'user_id' => $member->id,
            'status' => 'scheduled',
        ]);

        Appointment::factory()->create([
            'trainer_id' => $trainer->id,
            'user_id' => $member->id,
            'status' => 'completed',
        ]);

        Appointment::factory()->create([
            'trainer_id' => $trainer->id,
            'user_id' => $member->id,
            'status' => 'scheduled',
        ]);

        $scheduledAppointments = Appointment::where('status', 'scheduled')->get();
        $completedAppointments = Appointment::where('status', 'completed')->get();

        $this->assertCount(2, $scheduledAppointments);
        $this->assertCount(1, $completedAppointments);
    }

    // ==================== Notes ====================

    public function test_appointment_notes_can_be_null(): void
    {
        $appointment = Appointment::factory()->create(['notes' => null]);

        $this->assertNull($appointment->notes);
    }

    public function test_appointment_notes_can_be_set(): void
    {
        $appointment = Appointment::factory()->create([
            'notes' => 'Bring water bottle and towel',
        ]);

        $this->assertEquals('Bring water bottle and towel', $appointment->notes);
    }

    // ==================== Trainer Appointments ====================

    public function test_trainer_can_have_multiple_appointments(): void
    {
        $trainer = User::factory()->trainer()->create();
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();

        Appointment::factory()->create([
            'trainer_id' => $trainer->id,
            'user_id' => $member1->id,
        ]);

        Appointment::factory()->create([
            'trainer_id' => $trainer->id,
            'user_id' => $member2->id,
        ]);

        $this->assertCount(2, $trainer->trainerAppointments);
    }

    // ==================== Member Appointments ====================

    public function test_member_can_have_multiple_appointments(): void
    {
        $trainer1 = User::factory()->trainer()->create();
        $trainer2 = User::factory()->trainer()->create();
        $member = User::factory()->create();

        Appointment::factory()->create([
            'trainer_id' => $trainer1->id,
            'user_id' => $member->id,
        ]);

        Appointment::factory()->create([
            'trainer_id' => $trainer2->id,
            'user_id' => $member->id,
        ]);

        $this->assertCount(2, $member->userAppointments);
    }
}
