<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->toISOString(),
            'gender' => $this->gender,
            'height' => $this->height,
            'weight' => $this->weight,
            'fitness_goal' => $this->fitness_goal,
            'activity_level' => $this->activity_level,
            'avatar' => $this->avatar,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
