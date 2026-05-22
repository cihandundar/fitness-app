<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserEquipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'equipment_type_id' => $this->equipment_type_id,
            'created_at' => $this->created_at?->toISOString(),

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'equipment_type' => new EquipmentTypeResource($this->whenLoaded('equipmentType')),
        ];
    }
}
