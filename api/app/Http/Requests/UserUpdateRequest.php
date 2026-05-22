<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Kullanıcı kendi profilini güncelleyebilir, admin herkesi
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $userId,
            'role' => 'sometimes|required|in:user,trainer,admin,super_admin',
            'is_active' => 'boolean',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string|max:500',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'İsim gereklidir',
            'name.max' => 'İsim 255 karakteri geçemez',
            'email.required' => 'E-posta gereklidir',
            'email.email' => 'Geçerli bir e-posta adresi giriniz',
            'email.unique' => 'Bu e-posta zaten kullanımda',
            'role.required' => 'Rol gereklidir',
            'role.in' => 'Rol geçersiz',
            'phone.max' => 'Telefon numarası 20 karakteri geçemez',
            'avatar.max' => 'Avatar URL 500 karakteri geçemez',
        ];
    }
}
