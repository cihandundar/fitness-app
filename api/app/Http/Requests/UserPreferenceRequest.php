<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'fitness_level' => 'nullable|in:beginner,intermediate,advanced',
            'fitness_goal' => 'nullable|in:weight_loss,muscle_gain,endurance,flexibility,general',
            'workouts_per_week' => 'nullable|integer|min:1|max:7',
            'available_equipment' => 'nullable|array',
            'available_equipment.*' => 'integer|exists:equipment_types,id',
            'target_muscle_groups' => 'nullable|array',
            'target_muscle_groups.*' => 'integer|exists:muscle_groups,id',
            'notification_preferences' => 'nullable|array',
            'notification_preferences.email_reminders' => 'boolean',
            'notification_preferences.workout_reminders' => 'boolean',
            'notification_preferences.progress_updates' => 'boolean',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'fitness_level.in' => 'Fitness seviyesi geçersiz',
            'fitness_goal.in' => 'Fitness hedefi geçersiz',
            'workouts_per_week.integer' => 'Haftalık antrenman sayısı tam sayı olmalıdır',
            'workouts_per_week.min' => 'Haftada en az 1 antrenman',
            'workouts_per_week.max' => 'Haftada en fazla 7 antrenman',
            'available_equipment.array' => 'Ekipman listesi dizi olmalıdır',
            'target_muscle_groups.array' => 'Hedef kas grupları dizi olmalıdır',
        ];
    }
}
