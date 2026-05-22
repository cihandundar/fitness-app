<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkoutStartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'workout_id' => 'required|exists:workouts,id',
            'scheduled_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'workout_id.required' => 'Antrenman ID gereklidir',
            'workout_id.exists' => 'Antrenman bulunamadı',
            'scheduled_date.date' => 'Planlanan tarih geçersiz',
            'notes.max' => 'Notlar 500 karakteri geçemez',
        ];
    }
}
