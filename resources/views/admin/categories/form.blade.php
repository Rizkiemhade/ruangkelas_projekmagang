@extends('layouts.admin')

@section('title', $category->exists ? 'Ubah Kategori' : 'Tambah Kategori')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $category->exists ? 'Ubah kategori' : 'Tambah kategori' }}</h1>
            <p class="page-subtitle">Nama yang jelas membantu peserta memilih kelas yang sesuai.</p>
        </div>
        <div class="page-header-actions">
            <a class="btn-custom btn-custom-outline-secondary" href="{{ route('admin.categories.index') }}">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali
            </a>
        </div>
    </div>

    <form class="card admin-panel admin-form category-form" method="post" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
        <div class="card-header">
            <div>
                <h2 class="card-title">Informasi kategori</h2>
                <p class="page-subtitle">Isi nama dan deskripsi kategori.</p>
            </div>
        </div>
        <div class="admin-panel-body">
            @csrf
            @if ($category->exists) @method('put') @endif
            <label>Nama kategori
                <input class="form-control-custom" name="name" value="{{ old('name', $category->name) }}" required maxlength="100">
                @error('name')<small class="field-error">{{ $message }}</small>@enderror
            </label>
            <label>Deskripsi singkat
                <textarea class="form-control-custom" name="description" rows="4" maxlength="2000">{{ old('description', $category->description) }}</textarea>
                @error('description')<small class="field-error">{{ $message }}</small>@enderror
            </label>
            <div class="form-actions">
                <a class="btn-custom btn-custom-outline-secondary" href="{{ route('admin.categories.index') }}">Batal</a>
                <button class="btn-custom btn-custom-primary" type="submit">{{ $category->exists ? 'Simpan perubahan' : 'Simpan kategori' }}</button>
            </div>
        </div>
    </form>
@endsection
