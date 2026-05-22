<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciseLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'completed_workout_id',
        'exercise_id',
        'set_number',
        'weight',
        'reps',
        'rest_time',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'weight' => 'float',
    ];

    public function completedWorkout()
    {
        return $this->belongsTo(CompletedWorkout::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
