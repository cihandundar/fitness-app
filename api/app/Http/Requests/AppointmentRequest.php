<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Kullanıcı kendi randevusunu oluşturabilir
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'trainer_id' => 'required|exists:users,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string|max:1000',
            'status' => 'in:pending,confirmed,cancelled,completed',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'trainer_id.required' => 'Eğitmen seçilmelidir',
            'trainer_id.exists' => 'Seçilen eğitmen bulunamadı',
            'start_time.required' => 'Başlangıç tarihi gereklidir',
            'start_time.after' => 'Randevu tarihi gelecek bir tarih olmalıdır',
            'end_time.required' => 'Bitiş tarihi gereklidir',
            'end_time.after' => 'Bitiş tarihi başlangıçtan sonra olmalıdır',
            'notes.max' => 'Notlar 1000 karakteri geçemez',
            'status.in' => 'Durum geçersiz',
        ];
    }
}
