<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $program = $this->route('program');
        $programId = $program instanceof \App\Models\Program ? $program->id : null;

        return [
            'title' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:programs,slug,' . $programId,
            'description' => 'nullable|string',
            'level' => $isUpdate ? 'sometimes|required|in:beginner,intermediate,advanced' : 'required|in:beginner,intermediate,advanced',
            'duration_weeks' => $isUpdate ? 'sometimes|required|integer|min:1|max:52' : 'required|integer|min:1|max:52',
            'image' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'settings' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Program başlığı zorunludur.',
            'level.required' => 'Seviye zorunludur.',
            'level.in' => 'Seviye beginner, intermediate veya advanced olmalıdır.',
            'duration_weeks.required' => 'Süre zorunludur.',
            'duration_weeks.min' => 'Süre en az 1 hafta olmalıdır.',
            'duration_weeks.max' => 'Süre en fazla 52 hafta olabilir.',
        ];
    }
}
