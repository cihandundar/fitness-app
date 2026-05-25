<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterCompleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->email)),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|min:10|max:20',
            'gender' => 'required|in:male,female',
            'birth_date' => 'required|date|before:16 years ago',
            'height' => 'required|numeric|min:100|max:250',
            'weight' => 'required|numeric|min:30|max:200',
            'fitness_goal' => 'nullable|in:weight_loss,muscle_gain,stay_fit,strength,flexibility',
            'preferred_branches' => 'nullable|array',
            'preferred_branches.*' => 'integer|exists:branches,id',
            'membership_plan_id' => 'required|exists:membership_plans,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ad soyad zorunludur.',
            'email.required' => 'E-posta zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'password.required' => 'Şifre zorunludur.',
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Şifreler eşleşmiyor.',
            'phone.required' => 'Telefon numarası zorunludur.',
            'phone.min' => 'Telefon numarası en az 10 karakter olmalıdır.',
            'gender.required' => 'Cinsiyet seçilmelidir.',
            'gender.in' => 'Geçerli bir cinsiyet seçiniz.',
            'birth_date.required' => 'Doğum tarihi zorunludur.',
            'birth_date.before' => 'Kayıt için en az 16 yaşında olmalısınız.',
            'height.required' => 'Boy zorunludur.',
            'weight.required' => 'Kilo zorunludur.',
            'membership_plan_id.required' => 'Üyelik paketi seçilmelidir.',
            'membership_plan_id.exists' => 'Seçilen paket geçersiz.',
            'preferred_branches.*.exists' => 'Seçilen branş geçersiz.',
        ];
    }
}
