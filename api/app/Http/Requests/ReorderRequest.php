<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'orders' => 'required|array|min:1',
            'orders.*.id' => 'required|integer',
            'orders.*.order' => 'required|integer|min:0',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'orders.required' => 'Sıralama listesi gereklidir',
            'orders.array' => 'Sıralama listesi dizi olmalıdır',
            'orders.min' => 'En az bir öğe gereklidir',
            'orders.*.id.required' => 'ID gereklidir',
            'orders.*.id.integer' => 'ID tam sayı olmalıdır',
            'orders.*.order.required' => 'Sıra numarası gereklidir',
            'orders.*.order.integer' => 'Sıra numarası tam sayı olmalıdır',
            'orders.*.order.min' => 'Sıra numarası 0 veya pozitif olmalıdır',
        ];
    }
}
