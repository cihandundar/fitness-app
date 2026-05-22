<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'completed_workout_id' => $this->completed_workout_id,
            'workout_exercise_id' => $this->workout_exercise_id,
            'set_number' => $this->set_number,
            'weight' => $this->weight,
            'reps' => $this->reps,
            'duration_seconds' => $this->duration_seconds,
            'rpe' => $this->rpe,
            'distance' => $this->distance,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'completed_workout' => new CompletedWorkoutResource($this->whenLoaded('completedWorkout')),
        ];
    }
}
