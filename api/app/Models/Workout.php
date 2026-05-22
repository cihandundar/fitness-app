<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'title',
        'description',
        'day_number',
        'duration_minutes',
    ];

    // hangi programa ait
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    // workoutun egzersizleri
    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'workout_exercises')
                    ->withPivot('sets', 'reps', 'rest_seconds', 'order')
                    ->orderBy('order')
                    ->withTimestamps();
    }
}
