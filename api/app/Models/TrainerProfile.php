<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'specialties',
        'experience_years',
        'hourly_rate',
        'is_verified',
    ];

    protected $casts = [
        'specialties' => 'array',
        'is_verified' => 'boolean',
        'hourly_rate' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
