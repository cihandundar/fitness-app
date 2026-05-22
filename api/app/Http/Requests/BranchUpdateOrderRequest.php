<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BranchUpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'orders' => 'required|array|min:1',
            'orders.*.id' => 'required|integer|exists:branches,id',
            'orders.*.order' => 'required|integer|min:0',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'orders.required' => 'Sıralama listesi gereklidir',
            'orders.array' => 'Sıralama listesi dizi olmalıdır',
            'orders.min' => 'En az bir branş sıralaması gereklidir',
            'orders.*.id.required' => 'Branş ID gereklidir',
            'orders.*.id.integer' => 'Branş ID tam sayı olmalıdır',
            'orders.*.id.exists' => 'Branş bulunamadı',
            'orders.*.order.required' => 'Sıra numarası gereklidir',
            'orders.*.order.integer' => 'Sıra numarası tam sayı olmalıdır',
            'orders.*.order.min' => 'Sıra numarası 0 veya pozitif olmalıdır',
        ];
    }
}
