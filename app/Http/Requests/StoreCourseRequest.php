<?php

namespace App\Http\Requests;

use App\Enums\CourseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:10000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'instructor' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'image' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::enum(CourseStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul kelas wajib diisi.',
            'description.required' => 'Deskripsi kelas wajib diisi.',
            'category_id.required' => 'Silakan pilih kategori.',
            'category_id.exists' => 'Kategori yang dipilih tidak tersedia.',
            'instructor.required' => 'Nama instruktur wajib diisi.',
            'price.required' => 'Harga kelas wajib diisi; gunakan 0 untuk kelas gratis.',
            'price.min' => 'Harga kelas tidak boleh negatif.',
            'image.image' => 'File gambar harus berupa gambar yang valid.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
            'status.required' => 'Status kelas wajib dipilih.',
        ];
    }
}
