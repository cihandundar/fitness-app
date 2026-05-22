<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkoutLogSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'completed_workout_id' => 'required|exists:completed_workouts,id',
            'workout_exercise_id' => 'required|exists:workout_exercises,id',
            'set_number' => 'required|integer|min:1',
            'weight' => 'nullable|numeric|min:0',
            'reps' => 'required|integer|min:1',
            'duration_seconds' => 'nullable|integer|min:0',
            'rpe' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string|max:500',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'completed_workout_id.required' => 'Tamamlanan antrenman ID gereklidir',
            'completed_workout_id.exists' => 'Tamamlanan antrenman bulunamadı',
            'workout_exercise_id.required' => 'Antrenman egzersizi ID gereklidir',
            'workout_exercise_id.exists' => 'Antrenman egzersizi bulunamadı',
            'set_number.required' => 'Set numarası gereklidir',
            'set_number.integer' => 'Set numarası tam sayı olmalıdır',
            'set_number.min' => 'Set numarası en az 1 olmalıdır',
            'weight.numeric' => 'Ağırlık sayı olmalıdır',
            'weight.min' => 'Ağırlık 0 veya pozitif olmalıdır',
            'reps.required' => 'Tekrar sayısı gereklidir',
            'reps.integer' => 'Tekrar sayısı tam sayı olmalıdır',
            'reps.min' => 'En az 1 tekrar yapılmalıdır',
            'duration_seconds.integer' => 'Süre tam sayı olmalıdır (saniye)',
            'duration_seconds.min' => 'Süre 0 veya pozitif olmalıdır',
            'rpe.integer' => 'RPE (zorluk) tam sayı olmalıdır',
            'rpe.min' => 'RPE en az 1 olmalıdır',
            'rpe.max' => 'RPE en fazla 10 olmalıdır',
            'notes.max' => 'Notlar 500 karakteri geçemez',
        ];
    }
}
