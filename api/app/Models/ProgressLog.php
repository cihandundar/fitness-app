<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'weight',
        'body_fat',
        'notes',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
            'weight' => 'float',
            'body_fat' => 'float',
        ];
    }

    // hangi kullanıcıya ait
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
