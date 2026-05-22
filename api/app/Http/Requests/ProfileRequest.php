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

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $userId,
            'height' => 'nullable|numeric|min:50|max:300',
            'weight' => 'nullable|numeric|min:20|max:300',
            'birth_date' => 'nullable|date',
            'avatar' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'İsim zorunludur.',
            'email.required' => 'E-posta zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta adresi zaten kullanımda.',
            'height.numeric' => 'Boy sayısal olmalıdır.',
            'height.min' => 'Boy en az 50 cm olabilir.',
            'height.max' => 'Boy en fazla 300 cm olabilir.',
            'weight.numeric' => 'Kilo sayısal olmalıdır.',
            'weight.min' => 'Kilo en az 20 kg olabilir.',
            'weight.max' => 'Kilo en fazla 300 kg olabilir.',
        ];
    }
}
