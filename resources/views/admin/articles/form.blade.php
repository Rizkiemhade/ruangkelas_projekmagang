@extends('layouts.admin')

@section('title', $article->exists ? 'Ubah Artikel' : 'Tulis Artikel')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $article->exists ? 'Ubah artikel' : 'Tulis artikel' }}</h1>
            <p class="page-subtitle">Artikel terbit akan muncul di halaman publik RuangKelas.</p>
        </div>
        <div class="page-header-actions">
            <a class="btn-custom btn-custom-outline-secondary" href="{{ route('admin.articles.index') }}">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali
            </a>
        </div>
    </div>

    <form class="card admin-panel admin-form edit-form" method="post" enctype="multipart/form-data" action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}">
        <div class="card-header">
            <div>
                <h2 class="card-title">Konten artikel</h2>
                <p class="page-subtitle">Isi konten dengan bahasa yang jelas dan mudah dipahami.</p>
            </div>
        </div>
        <div class="admin-panel-body">
            @csrf
            @if ($article->exists) @method('put') @endif
            <div class="form-grid">
                <label class="form-span">Judul artikel
                    <input class="form-control-custom" name="title" value="{{ old('title', $article->title) }}" required maxlength="255" placeholder="Contoh: Cara Memulai Belajar Pemrograman">
                    @error('title')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label>Nama penulis
                    <input class="form-control-custom" name="author_name" value="{{ old('author_name', $article->author_name ?: auth()->user()->name) }}" required maxlength="120">
                    @error('author_name')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label>Status artikel
                    <select class="form-control-custom" name="status" required>
                        <option value="draft" @selected(old('status', $article->published_at && $article->published_at->lte(now()) ? 'published' : 'draft') === 'draft')>Draf — belum tampil ke pengunjung</option>
                        <option value="published" @selected(old('status', $article->published_at && $article->published_at->lte(now()) ? 'published' : 'draft') === 'published')>Terbitkan — tampil di halaman artikel</option>
                    </select>
                    @error('status')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label class="form-span">Ringkasan
                    <textarea class="form-control-custom" name="excerpt" rows="3" required maxlength="500" placeholder="Tulis ringkasan singkat yang akan terlihat di daftar artikel...">{{ old('excerpt', $article->excerpt) }}</textarea>
                    <small>Maksimal 500 karakter.</small>
                    @error('excerpt')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label class="form-span">Thumbnail artikel <span>(opsional)</span>
                    <input class="form-control-custom" type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <small>Format JPG, PNG, atau WebP; maksimal 2 MB.</small>
                    @error('thumbnail')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label class="form-span">Upload dokumen artikel <span>(opsional)</span>
                    <input class="form-control-custom" type="file" name="article_file" accept=".txt,.docx,text/plain,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                    <small>Format .TXT atau .DOCX, maksimal 5 MB. Teks di dalam dokumen akan diimpor sebagai isi artikel, bukan disimpan sebagai lampiran.</small>
                    @error('article_file')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label class="form-span">Isi artikel
                    <textarea class="form-control-custom" name="body" rows="16" maxlength="50000" placeholder="Tulis isi artikel di sini, atau upload dokumen di atas...">{{ old('body', $article->body) }}</textarea>
                    <small>Isi dokumen yang di-upload akan menggantikan teks di kolom ini. Gunakan baris baru untuk memisahkan paragraf; maksimal 50.000 karakter.</small>
                    @error('body')<small class="field-error">{{ $message }}</small>@enderror
                </label>
            </div>
            @if ($article->thumbnail_path)
                <p class="current-image">Thumbnail saat ini: <a href="{{ Storage::disk('public')->url($article->thumbnail_path) }}" target="_blank" rel="noopener">lihat gambar</a>. Unggah file baru untuk menggantinya.</p>
            @endif
            <div class="form-actions">
                <a class="btn-custom btn-custom-outline-secondary" href="{{ route('admin.articles.index') }}">Batal</a>
                <button class="btn-custom btn-custom-primary" type="submit">
                    {{ $article->exists ? 'Simpan perubahan' : 'Simpan artikel' }}
                </button>
            </div>
        </div>
    </form>
@endsection
