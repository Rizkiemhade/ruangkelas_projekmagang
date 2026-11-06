@extends('layouts.admin')

@section('title', $course->exists ? 'Ubah Kelas' : 'Tambah Kelas')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $course->exists ? 'Ubah kelas' : 'Tambah kelas' }}</h1>
            <p class="page-subtitle">Lengkapi informasi kelas agar mudah ditemukan calon peserta.</p>
        </div>
        <div class="page-header-actions">
            <a class="btn-custom btn-custom-outline-secondary" href="{{ route('admin.courses.index') }}">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali
            </a>
        </div>
    </div>

    <form class="card admin-panel admin-form edit-form" method="post" enctype="multipart/form-data" action="{{ $course->exists ? route('admin.courses.update', $course) : route('admin.courses.store') }}">
        <div class="card-header">
            <div>
                <h2 class="card-title">Informasi kelas</h2>
                <p class="page-subtitle">Kolom bertanda wajib diisi sebelum disimpan.</p>
            </div>
        </div>
        <div class="admin-panel-body">
            @csrf
            @if ($course->exists) @method('put') @endif
            <div class="form-grid">
                <label>Judul kelas
                    <input class="form-control-custom" name="title" value="{{ old('title', $course->title) }}" required maxlength="180">
                    @error('title')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label>Kategori
                    <select class="form-control-custom" name="category_id" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $course->category_id) === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label>Nama instruktur
                    <input class="form-control-custom" name="instructor" value="{{ old('instructor', $course->instructor) }}" required maxlength="120">
                    @error('instructor')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label>Harga (Rp)
                    <input class="form-control-custom" type="number" name="price" value="{{ old('price', $course->price ?? 0) }}" min="0" max="99999999.99" step="1000" required>
                    <small>Masukkan 0 untuk kelas gratis.</small>
                    @error('price')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label class="form-span">Deskripsi
                    <textarea class="form-control-custom" name="description" rows="7" required maxlength="10000">{{ old('description', $course->description) }}</textarea>
                    @error('description')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label>Gambar kelas
                    <input class="form-control-custom" type="file" name="image" accept="image/*">
                    <small>Format gambar, maksimal 2 MB.</small>
                    @error('image')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label>Status kelas
                    <select class="form-control-custom" name="status" required>
                        @foreach (\App\Enums\CourseStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $course->status?->value ?? 'draft') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    @error('status')<small class="field-error">{{ $message }}</small>@enderror
                </label>
            </div>
            @if ($course->image_path)
                <p class="current-image">Gambar saat ini: <a href="{{ Storage::disk('public')->url($course->image_path) }}" target="_blank" rel="noopener">lihat gambar</a>. Unggah file baru untuk menggantinya.</p>
            @endif
            <div class="form-actions">
                <a class="btn-custom btn-custom-outline-secondary" href="{{ route('admin.courses.index') }}">Batal</a>
                <button class="btn-custom btn-custom-primary" type="submit">{{ $course->exists ? 'Simpan perubahan' : 'Simpan kelas' }}</button>
            </div>
        </div>
    </form>
@endsection
