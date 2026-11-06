<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    public function rules(): array
    {
        $changePassword = $this->filled('password');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()),
            ],
            'current_password' => [
                Rule::requiredIf($changePassword),
                Rule::when($changePassword, ['current_password'], ['nullable']),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'dimensions:max_width=3000,max_height=3000',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'profile_photo.image' => 'File foto profil harus berupa gambar yang valid.',
            'profile_photo.mimes' => 'Foto profil harus berformat JPG, PNG, atau WebP.',
            'profile_photo.extensions' => 'Foto profil harus berformat JPG, PNG, atau WebP.',
            'profile_photo.dimensions' => 'Dimensi foto profil maksimal 3000 × 3000 piksel.',
            'profile_photo.max' => 'Ukuran foto profil maksimal 2 MB.',
        ];
    }
}
