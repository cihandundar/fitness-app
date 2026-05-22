<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTargetGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'muscle_group_id' => $this->muscle_group_id,
            'created_at' => $this->created_at?->toISOString(),

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'muscle_group' => new MuscleGroupResource($this->whenLoaded('muscleGroup')),
        ];
    }
}
