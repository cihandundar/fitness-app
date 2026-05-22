<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'muscle_group' => $this->muscleGroup?->name ?? $this->muscle_group,
            'equipment' => $this->equipmentType?->name ?? $this->equipment,
            'muscle_group_id' => $this->muscle_group_id,
            'equipment_type_id' => $this->equipment_type_id,
            'difficulty' => $this->difficulty,
            'instructions' => $this->instructions,
            'tips' => $this->tips,
            'image' => $this->image,
            'video_url' => $this->video_url,
            'is_active' => $this->is_active ?? true,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'workouts_count' => $this->whenCounted('workouts'),
            'pivot' => $this->when($this->pivot !== null, [
                'sets' => $this->pivot->sets ?? null,
                'reps' => $this->pivot->reps ?? null,
                'rest_seconds' => $this->pivot->rest_seconds ?? null,
                'order' => $this->pivot->order ?? null,
            ]),
        ];
    }
}
