<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:500'],
            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'body' => [
                Rule::requiredIf(! $this->hasFile('article_file')),
                'nullable',
                'string',
                'max:50000',
            ],
            'author_name' => ['required', 'string', 'max:120'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'article_file' => [
                'nullable',
                'file',
                'mimes:txt,docx',
                'extensions:txt,docx',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul artikel wajib diisi.',
            'excerpt.required' => 'Ringkasan artikel wajib diisi.',
            'thumbnail.image' => 'Thumbnail harus berupa file gambar yang valid.',
            'thumbnail.mimes' => 'Thumbnail harus berupa gambar JPG, PNG, atau WebP.',
            'thumbnail.extensions' => 'Ekstensi thumbnail harus .jpg, .jpeg, .png, atau .webp.',
            'thumbnail.max' => 'Ukuran thumbnail maksimal 2 MB.',
            'body.required' => 'Isi artikel wajib diisi.',
            'author_name.required' => 'Nama penulis wajib diisi.',
            'status.required' => 'Pilih apakah artikel disimpan sebagai draf atau diterbitkan.',
            'status.in' => 'Status artikel tidak valid.',
            'article_file.mimes' => 'Dokumen artikel harus berupa file TXT atau DOCX yang valid.',
            'article_file.extensions' => 'Dokumen artikel harus berformat .txt atau .docx.',
            'article_file.max' => 'Ukuran dokumen artikel maksimal 5 MB.',
        ];
    }
}
