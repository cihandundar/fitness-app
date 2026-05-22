<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MembershipPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        $rules = [
            'name' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'price' => $isUpdate ? 'sometimes|required|numeric|min:0' : 'required|numeric|min:0',
            'type' => $isUpdate ? 'sometimes|required|in:gym,hybrid,pt' : 'required|in:gym,hybrid,pt',
            'features' => $isUpdate ? 'sometimes|required|array|min:1' : 'required|array|min:1',
            'features.*' => 'string|max:255',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'duration_days' => 'nullable|integer|min:1',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Plan adı gereklidir',
            'name.max' => 'Plan adı 255 karakteri geçemez',
            'price.required' => 'Fiyat gereklidir',
            'price.numeric' => 'Fiyat sayı olmalıdır',
            'price.min' => 'Fiyat 0 veya daha büyük olmalıdır',
            'type.required' => 'Plan türü gereklidir',
            'type.in' => 'Plan türü geçersiz',
            'features.required' => 'Özellikler gereklidir',
            'features.array' => 'Özellikler dizi olmalıdır',
            'duration_days.integer' => 'Süre gün sayısı olmalıdır',
            'duration_days.min' => 'Süre en az 1 gün olmalıdır',
        ];
    }
}
