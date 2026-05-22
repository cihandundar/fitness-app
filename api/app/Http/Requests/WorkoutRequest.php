<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'program_id' => $isUpdate ? 'sometimes|required|exists:programs,id' : 'required|exists:programs,id',
            'day_number' => $isUpdate ? 'sometimes|required|integer|min:1|max:7' : 'required|integer|min:1|max:7',
            'title' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => $isUpdate ? 'sometimes|required|integer|min:1|max:180' : 'required|integer|min:1|max:180',
        ];
    }

    public function messages(): array
    {
        return [
            'program_id.required' => 'Program seçimi zorunludur.',
            'program_id.exists' => 'Seçilen program bulunamadı.',
            'day_number.required' => 'Gün numarası zorunludur.',
            'day_number.min' => 'Gün numarası en az 1 olmalıdır.',
            'day_number.max' => 'Gün numarası en fazla 7 olabilir.',
            'title.required' => 'Antrenman başlığı zorunludur.',
            'duration_minutes.required' => 'Süre zorunludur.',
            'duration_minutes.min' => 'Süre en az 1 dakika olmalıdır.',
            'duration_minutes.max' => 'Süre en fazla 180 dakika olabilir.',
        ];
    }
}
