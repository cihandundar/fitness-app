<?php

namespace App\Models;

use App\Models\Program;
use App\Models\ProgressLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'height',
        'weight',
        'birth_date',
        'date_of_birth',
        'is_active',
        'phone',
        'gender',
        'fitness_goal',
        'activity_level',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'birth_date' => 'date',
            'date_of_birth' => 'date',
            'height' => 'decimal:2',
            'weight' => 'decimal:2',
            'last_login_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isTrainer(): bool
    {
        return $this->role === 'trainer';
    }

    public function trainerProfile()
    {
        return $this->hasOne(TrainerProfile::class);
    }

    // Bir üyenin bağlı olduğu eğitmenler
    public function trainers()
    {
        return $this->belongsToMany(User::class, 'trainer_clients', 'client_id', 'trainer_id')
                    ->withPivot('status', 'started_at', 'ended_at', 'notes', 'total_days', 'remaining_days', 'last_check_in')
                    ->withTimestamps();
    }

    // Bir eğitmenin bağlı olduğu üyeler (danışanlar)
    public function clients()
    {
        return $this->belongsToMany(User::class, 'trainer_clients', 'trainer_id', 'client_id')
                    ->withPivot('status', 'started_at', 'ended_at', 'notes', 'total_days', 'remaining_days', 'last_check_in')
                    ->withTimestamps();
    }

    // Eğitmen olarak randevularım
    public function trainerAppointments()
    {
        return $this->hasMany(Appointment::class, 'trainer_id');
    }

    // Üye olarak randevularım
    public function userAppointments()
    {
        return $this->hasMany(Appointment::class, 'user_id');
    }

    public function memberships()
    {
        return $this->hasMany(UserMembership::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function programs()
    {
        return $this->belongsToMany(Program::class, 'user_programs')
                    ->withPivot('assigned_by', 'started_at', 'completed_at', 'is_active')
                    ->withTimestamps();
    }

    public function progressLogs()
    {
        return $this->hasMany(ProgressLog::class);
    }

    public function completedWorkouts()
    {
        return $this->hasMany(CompletedWorkout::class);
    }

    public function equipments()
    {
        return $this->belongsToMany(EquipmentType::class, 'user_equipment');
    }

    public function targetGroups()
    {
        return $this->belongsToMany(MuscleGroup::class, 'user_target_groups');
    }
}
