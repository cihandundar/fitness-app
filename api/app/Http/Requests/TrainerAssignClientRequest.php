<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrainerAssignClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sadece trainer ve admin
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'user_id' => 'required|exists:users,id|different:trainer_id',
            'trainer_id' => 'required|exists:trainer_profiles,id',
            'program_id' => 'nullable|exists:programs,id',
            'notes' => 'nullable|string|max:1000',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Kullanıcı ID gereklidir',
            'user_id.exists' => 'Kullanıcı bulunamadı',
            'user_id.different' => 'Kendi kendinizi atayamazsınız',
            'trainer_id.required' => 'Eğitmen ID gereklidir',
            'trainer_id.exists' => 'Eğitmen bulunamadı',
            'program_id.exists' => 'Program bulunamadı',
            'notes.max' => 'Notlar 1000 karakteri geçemez',
        ];
    }
}
