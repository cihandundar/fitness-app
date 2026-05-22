<?php

namespace App\Models;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'title',
        'slug',
        'description',
        'level',
        'duration_weeks',
        'image',
        'is_active',
        'is_featured',
        'is_custom',
        'target_user_id',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_custom' => 'boolean',
            'settings' => 'array',
        ];
    }

    // programı oluşturan admin
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Eğer özel bir programsa, hedef kullanıcı
    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    // programa kayıtlı kullanıcılar
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_programs')
                    ->withPivot('assigned_by', 'started_at', 'completed_at', 'is_active')
                    ->withTimestamps();
    }

    // programın workoutları
    public function workouts()
    {
        return $this->hasMany(Workout::class);
    }
}
