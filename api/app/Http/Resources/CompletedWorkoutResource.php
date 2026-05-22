<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompletedWorkoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'workout_id' => $this->workout_id,
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'total_duration_seconds' => $this->total_duration_seconds,
            'calories_burned' => $this->calories_burned,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'workout' => new WorkoutResource($this->whenLoaded('workout')),
            'exercise_logs' => ExerciseLogResource::collection($this->whenLoaded('exerciseLogs')),
        ];
    }
}
