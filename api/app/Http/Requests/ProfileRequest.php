<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|min:10|max:20',
            'height' => 'sometimes|nullable|numeric|min:100|max:250',
            'weight' => 'sometimes|nullable|numeric|min:30|max:200',
            'birth_date' => 'sometimes|nullable|date|before:16 years ago',
            'date_of_birth' => 'sometimes|nullable|date|before:16 years ago',
            'gender' => 'sometimes|in:male,female',
            'fitness_goal' => 'nullable|in:weight_loss,muscle_gain,stay_fit,strength,flexibility',
            'avatar' => 'nullable|string|max:500',
            'preferred_branches' => 'nullable|array',
            'preferred_branches.*' => 'integer|exists:branches,id',
        ];

        if ($userId) {
            $rules['email'] = 'sometimes|required|email|unique:users,email,' . $userId;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'İsim zorunludur.',
            'email.required' => 'E-posta zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta adresi zaten kullanımda.',
            'phone.required' => 'Telefon numarası zorunludur.',
            'phone.min' => 'Telefon numarası en az 10 karakter olmalıdır.',
            'height.numeric' => 'Boy sayısal olmalıdır.',
            'height.min' => 'Boy en az 100 cm olabilir.',
            'height.max' => 'Boy en fazla 250 cm olabilir.',
            'weight.numeric' => 'Kilo sayısal olmalıdır.',
            'weight.min' => 'Kilo en az 30 kg olabilir.',
            'weight.max' => 'Kilo en fazla 200 kg olabilir.',
            'birth_date.before' => 'En az 16 yaşında olmalısınız.',
            'gender.in' => 'Cinsiyet değeri geçerli olmalıdır.',
            'fitness_goal.in' => 'Fitness hedefi geçerli olmalıdır.',
        ];
    }
}
