<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\MuscleGroup;
use App\Models\EquipmentType;
use App\Models\Program;
use App\Models\ProgressLog;
use App\Models\Workout;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\Branch;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // ==================== Basic Attributes ====================

    public function test_user_has_correct_fillable_attributes(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('user', $user->role);
        $this->assertTrue($user->is_active);
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plaintext123',
        ]);

        $this->assertNotEquals('plaintext123', $user->password);
        $this->assertTrue(\Hash::check('plaintext123', $user->password));
    }

    public function test_user_has_correct_hidden_attributes(): void
    {
        $user = User::factory()->make();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    public function test_user_casts_is_active_to_boolean(): void
    {
        $user = User::factory()->create(['is_active' => 1]);

        $this->assertIsBool($user->is_active);
        $this->assertTrue($user->is_active);
    }

    public function test_user_casts_dates_correctly(): void
    {
        $user = User::factory()->create([
            'birth_date' => '1990-01-01',
            'last_login_at' => now(),
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->birth_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->last_login_at);
    }

    // ==================== Role Methods ====================

    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assertTrue($admin->isAdmin());
    }

    public function test_is_admin_returns_true_for_super_admin_role(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->assertTrue($superAdmin->isAdmin());
    }

    public function test_is_admin_returns_false_for_regular_user(): void
    {
        $user = User::factory()->create();
        $this->assertFalse($user->isAdmin());
    }

    public function test_is_admin_returns_false_for_trainer(): void
    {
        $trainer = User::factory()->trainer()->create();
        $this->assertFalse($trainer->isAdmin());
    }

    public function test_is_trainer_returns_true_for_trainer_role(): void
    {
        $trainer = User::factory()->trainer()->create();
        $this->assertTrue($trainer->isTrainer());
    }

    public function test_is_trainer_returns_false_for_non_trainer(): void
    {
        $user = User::factory()->create();
        $this->assertFalse($user->isTrainer());
    }

    // ==================== Relationships - Trainers/Clients ====================

    public function test_user_can_have_multiple_trainers(): void
    {
        $user = User::factory()->create();
        $trainer1 = User::factory()->trainer()->create();
        $trainer2 = User::factory()->trainer()->create();

        $user->trainers()->attach([$trainer1->id, $trainer2->id], [
            'status' => 'active',
            'started_at' => now(),
            'total_days' => 30,
            'remaining_days' => 30,
        ]);

        $this->assertCount(2, $user->trainers);
        $this->assertTrue($user->trainers->contains($trainer1));
        $this->assertTrue($user->trainers->contains($trainer2));
    }

    public function test_trainer_can_have_multiple_clients(): void
    {
        $trainer = User::factory()->trainer()->create();
        $client1 = User::factory()->create();
        $client2 = User::factory()->create();

        $trainer->clients()->attach([$client1->id, $client2->id], [
            'status' => 'active',
            'started_at' => now(),
            'total_days' => 30,
            'remaining_days' => 30,
        ]);

        $this->assertCount(2, $trainer->clients);
        $this->assertTrue($trainer->clients->contains($client1));
        $this->assertTrue($trainer->clients->contains($client2));
    }

    public function test_trainer_client_pivot_includes_required_fields(): void
    {
        $trainer = User::factory()->trainer()->create();
        $client = User::factory()->create();

        $trainer->clients()->attach($client->id, [
            'status' => 'active',
            'started_at' => now(),
            'total_days' => 60,
            'remaining_days' => 45,
            'last_check_in' => now(),
        ]);

        $pivot = $trainer->clients()->first()->pivot;
        $this->assertEquals('active', $pivot->status);
        $this->assertEquals(60, $pivot->total_days);
        $this->assertEquals(45, $pivot->remaining_days);
    }

    // ==================== Relationships - Appointments ====================

    public function test_user_can_have_trainer_appointments(): void
    {
        $trainer = User::factory()->trainer()->create();
        $member = User::factory()->create();

        Appointment::factory()->create([
            'trainer_id' => $trainer->id,
            'user_id' => $member->id,
        ]);

        $this->assertCount(1, $trainer->trainerAppointments);
        $this->assertInstanceOf(Appointment::class, $trainer->trainerAppointments->first());
    }

    public function test_user_can_have_member_appointments(): void
    {
        $trainer = User::factory()->trainer()->create();
        $member = User::factory()->create();

        Appointment::factory()->create([
            'trainer_id' => $trainer->id,
            'user_id' => $member->id,
        ]);

        $this->assertCount(1, $member->userAppointments);
        $this->assertInstanceOf(Appointment::class, $member->userAppointments->first());
    }

    // ==================== Relationships - Memberships ====================

    public function test_user_can_have_multiple_memberships(): void
    {
        $user = User::factory()->create();
        $plan1 = MembershipPlan::factory()->create();
        $plan2 = MembershipPlan::factory()->create();

        \App\Models\UserMembership::factory()->create([
            'user_id' => $user->id,
            'membership_plan_id' => $plan1->id,
        ]);

        \App\Models\UserMembership::factory()->create([
            'user_id' => $user->id,
            'membership_plan_id' => $plan2->id,
        ]);

        $this->assertCount(2, $user->memberships);
    }

    public function test_user_can_have_payments(): void
    {
        $user = User::factory()->create();

        Payment::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertCount(1, $user->payments);
        $this->assertInstanceOf(Payment::class, $user->payments->first());
    }

    // ==================== Relationships - Programs ====================

    public function test_user_can_have_multiple_programs(): void
    {
        $user = User::factory()->create();
        $trainer = User::factory()->trainer()->create();
        $program1 = Program::factory()->create();
        $program2 = Program::factory()->create();

        $user->programs()->attach($program1->id, [
            'assigned_by' => $trainer->id,
            'is_active' => true,
            'started_at' => now(),
        ]);

        $user->programs()->attach($program2->id, [
            'assigned_by' => $trainer->id,
            'is_active' => true,
            'started_at' => now(),
        ]);

        $this->assertCount(2, $user->programs);
        $this->assertTrue($user->programs->contains($program1));
        $this->assertTrue($user->programs->contains($program2));
    }

    public function test_user_program_pivot_includes_trainer_info(): void
    {
        $user = User::factory()->create();
        $trainer = User::factory()->trainer()->create();
        $program = Program::factory()->create();

        $user->programs()->attach($program->id, [
            'assigned_by' => $trainer->id,
            'is_active' => true,
            'started_at' => now(),
        ]);

        $pivot = $user->programs()->first()->pivot;
        $this->assertEquals($trainer->id, $pivot->assigned_by);
        $this->assertEquals(1, $pivot->is_active);
    }

    // ==================== Relationships - Progress ====================

    public function test_user_can_have_multiple_progress_logs(): void
    {
        $user = User::factory()->create();

        ProgressLog::factory()->create(['user_id' => $user->id]);
        ProgressLog::factory()->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->progressLogs);
        $this->assertInstanceOf(ProgressLog::class, $user->progressLogs->first());
    }

    public function test_user_can_have_completed_workouts(): void
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create();

        \App\Models\CompletedWorkout::factory()->create([
            'user_id' => $user->id,
            'workout_id' => $workout->id,
        ]);

        $this->assertCount(1, $user->completedWorkouts);
        $this->assertInstanceOf(\App\Models\CompletedWorkout::class, $user->completedWorkouts->first());
    }

    // ==================== Relationships - Preferences ====================

    public function test_user_can_have_target_muscle_groups(): void
    {
        $user = User::factory()->create();
        $chest = MuscleGroup::factory()->create(['name' => 'Chest']);
        $back = MuscleGroup::factory()->create(['name' => 'Back']);

        $user->targetGroups()->attach([$chest->id, $back->id]);

        $this->assertCount(2, $user->targetGroups);
        $this->assertTrue($user->targetGroups->contains($chest));
        $this->assertTrue($user->targetGroups->contains($back));
    }

    public function test_user_can_have_equipment_types(): void
    {
        $user = User::factory()->create();
        $dumbbell = EquipmentType::factory()->create(['name' => 'Dumbbell']);
        $barbell = EquipmentType::factory()->create(['name' => 'Barbell']);

        $user->equipments()->attach([$dumbbell->id, $barbell->id]);

        $this->assertCount(2, $user->equipments);
        $this->assertTrue($user->equipments->contains($dumbbell));
        $this->assertTrue($user->equipments->contains($barbell));
    }

    // ==================== API Tokens ====================

    public function test_user_uses_sanctum_for_tokens(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token');

        $this->assertNotNull($token);
        $this->assertIsString($token->plainTextToken);
        $this->assertEquals('test-token', $token->accessToken->name);
    }

    public function test_user_can_delete_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('test-token');

        $user->tokens()->delete();

        $this->assertCount(0, $user->tokens);
    }

    // ==================== Email Verification ====================

    public function test_user_has_email_verified_at_field(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->assertNotNull($user->email_verified_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
    }

    public function test_user_can_be_unverified(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);
    }
}
