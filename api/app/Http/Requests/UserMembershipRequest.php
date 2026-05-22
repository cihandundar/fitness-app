<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'in:active,expired,cancelled',
            'payment_status' => 'in:pending,paid,failed',
            'trainer_id' => 'nullable|exists:trainer_profiles,id',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Kullanıcı ID gereklidir',
            'user_id.exists' => 'Kullanıcı bulunamadı',
            'membership_plan_id.required' => 'Üyelik planı gereklidir',
            'membership_plan_id.exists' => 'Üyelik planı bulunamadı',
            'start_date.required' => 'Başlangıç tarihi gereklidir',
            'end_date.required' => 'Bitiş tarihi gereklidir',
            'end_date.after' => 'Bitiş tarihi başlangıçtan sonra olmalıdır',
            'status.in' => 'Durum geçersiz',
            'payment_status.in' => 'Ödeme durumu geçersiz',
            'trainer_id.exists' => 'Eğitmen bulunamadı',
        ];
    }
}
