<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $exercise = $this->route('exercise');
        $exerciseId = $exercise instanceof \App\Models\Exercise ? $exercise->id : null;

        return [
            'name' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:exercises,slug,' . $exerciseId,
            'description' => 'nullable|string',
            'muscle_group_id' => 'nullable|exists:muscle_groups,id',
            'equipment_type_id' => 'nullable|exists:equipment_types,id',
            'difficulty' => 'nullable|in:beginner,intermediate,advanced',
            'instructions' => 'nullable|string',
            'tips' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'video_url' => 'nullable|string|max:500',
            'data' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Egzersiz adı zorunludur.',
            'muscle_group_id.exists' => 'Geçersiz kas grubu.',
            'equipment_type_id.exists' => 'Geçersiz ekipman türü.',
            'difficulty.in' => 'Geçersiz zorluk seviyesi.',
            'image.image' => 'Görsel dosyası yüklemelisiniz.',
            'image.max' => 'Görsel maksimum 2MB olmalı.',
        ];
    }
}
