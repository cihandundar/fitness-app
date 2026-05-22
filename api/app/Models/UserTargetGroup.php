<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTargetGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'muscle_group_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function muscleGroup()
    {
        return $this->belongsTo(MuscleGroup::class);
    }
}
