<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TrainerClient extends Pivot
{
    protected $table = 'trainer_clients';

    protected $fillable = [
        'trainer_id',
        'client_id',
        'status',
        'started_at',
        'ended_at',
        'notes',
        'total_days',
        'remaining_days',
        'last_check_in',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'last_check_in' => 'datetime',
        ];
    }

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
