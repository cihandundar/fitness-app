<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkoutFinishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'notes' => 'nullable|string|max:1000',
            'total_duration_seconds' => 'nullable|integer|min:0',
            'calories_burned' => 'nullable|integer|min:0',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'notes.max' => 'Notlar 1000 karakteri geçemez',
            'total_duration_seconds.integer' => 'Toplam süre tam sayı olmalıdır (saniye)',
            'total_duration_seconds.min' => 'Toplam süre 0 veya pozitif olmalıdır',
            'calories_burned.integer' => 'Yakılan kalori tam sayı olmalıdır',
            'calories_burned.min' => 'Yakılan kalori 0 veya pozitif olmalıdır',
        ];
    }
}
