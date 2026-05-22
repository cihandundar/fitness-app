<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'user_membership_id' => 'required|exists:user_memberships,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,credit_card,bank_transfer,online',
            'payment_date' => 'required|date',
            'status' => 'in:pending,completed,failed,refunded',
            'notes' => 'nullable|string|max:1000',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'user_membership_id.required' => 'Üyelik ID gereklidir',
            'user_membership_id.exists' => 'Üyelik bulunamadı',
            'amount.required' => 'Tutar gereklidir',
            'amount.numeric' => 'Tutar sayı olmalıdır',
            'amount.min' => 'Tutar 0 veya daha büyük olmalıdır',
            'payment_method.required' => 'Ödeme yöntemi gereklidir',
            'payment_method.in' => 'Ödeme yöntemi geçersiz',
            'payment_date.required' => 'Ödeme tarihi gereklidir',
            'status.in' => 'Durum geçersiz',
            'notes.max' => 'Notlar 1000 karakteri geçemez',
        ];
    }
}
