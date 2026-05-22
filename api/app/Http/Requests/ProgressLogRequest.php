<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgressLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'weight' => 'nullable|numeric|min:20|max:300',
            'body_fat' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
            'logged_at' => 'nullable|date',
            'workout_id' => 'nullable|exists:workouts,id',
            'completed' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'weight.numeric' => 'Ağırlık sayısal olmalıdır.',
            'weight.min' => 'Ağırlık en az 20 kg olabilir.',
            'weight.max' => 'Ağırlık en fazla 300 kg olabilir.',
            'body_fat.numeric' => 'Vücut yağ oranı sayısal olmalıdır.',
            'body_fat.min' => 'Vücut yağ oranı en az 0 olabilir.',
            'body_fat.max' => 'Vücut yağ oranı en fazla 100 olabilir.',
            'workout_id.exists' => 'Antrenman bulunamadı.',
        ];
    }
}
