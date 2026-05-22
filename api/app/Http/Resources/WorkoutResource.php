<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'program_id' => $this->program_id,
            'day_number' => $this->day_number,
            'title' => $this->title,
            'description' => $this->description,
            'duration_minutes' => $this->duration_minutes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'exercises_count' => $this->whenCounted('exercises'),
            'exercises' => ExerciseResource::collection($this->whenLoaded('exercises')),
            'program' => ProgramResource::make($this->whenLoaded('program')),
        ];
    }
}
