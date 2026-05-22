<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'specialization' => $this->specialization,
            'certifications' => $this->certifications,
            'experience_years' => $this->experience_years,
            'bio' => $this->bio,
            'hourly_rate' => $this->hourly_rate ? (float) $this->hourly_rate : null,
            'is_available' => $this->is_available,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'clients_count' => $this->whenCounted('clients'),
        ];
    }
}
