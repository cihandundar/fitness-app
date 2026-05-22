<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompletedWorkout extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workout_id',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }

    public function exerciseLogs()
    {
        return $this->hasMany(ExerciseLog::class, 'completed_workout_id');
    }
}
