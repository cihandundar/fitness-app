<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEquipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'equipment_type_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }
}
