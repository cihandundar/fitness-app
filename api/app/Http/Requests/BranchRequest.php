<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Branş adı gereklidir',
            'name.max' => 'Branş adı 255 karakteri geçemez',
            'description.max' => 'Açıklama 1000 karakteri geçemez',
            'order.integer' => 'Sıralama sayı olmalıdır',
            'order.min' => 'Sıralama 0 veya daha büyük olmalıdır',
        ];
    }
}
