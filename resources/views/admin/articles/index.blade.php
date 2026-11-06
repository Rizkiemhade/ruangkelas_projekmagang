@extends('layouts.admin')

@section('title', 'Kelola Artikel')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Artikel</h1>
            <p class="page-subtitle">Tulis artikel baru, simpan sebagai draf, atau terbitkan di halaman publik.</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.articles.create') }}" class="btn-custom btn-custom-primary">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Tulis artikel
            </a>
        </div>
    </div>

    <section class="card admin-panel">
        <div class="card-header">
            <div>
                <h2 class="card-title">Daftar artikel</h2>
                <p class="page-subtitle">Artikel terbit terlihat oleh pengunjung, sedangkan draf hanya terlihat di panel admin.</p>
            </div>
        </div>
        <div class="admin-panel-body">
            <form class="admin-search" action="{{ route('admin.articles.index') }}" method="get">
                <label class="search-field">
                    <span aria-hidden="true">⌕</span>
                    <input class="form-control-custom" type="search" name="search" value="{{ $search }}" placeholder="Cari judul, ringkasan, atau penulis..." aria-label="Cari artikel">
                </label>
                <button class="btn-custom btn-custom-primary" type="submit">Cari</button>
                @if ($search)
                    <a class="btn-custom btn-custom-outline-secondary" href="{{ route('admin.articles.index') }}">Reset</a>
                @endif
            </form>

            @if ($articles->isNotEmpty())
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Artikel</th>
                                <th>Penulis</th>
                                <th>Status</th>
                                <th>Tanggal terbit</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($articles as $article)
                                <tr>
                                    <td>
                                        <span class="table-strong">{{ $article->title }}</span>
                                        <span class="table-subtitle">{{ \Illuminate\Support\Str::limit($article->excerpt, 90) }}</span>
                                    </td>
                                    <td>{{ $article->author_name }}</td>
                                    <td>
                                        @if ($article->published_at && $article->published_at->lte(now()))
                                            <span class="status-badge status-published">Terbit</span>
                                        @else
                                            <span class="status-badge status-draft">Draf</span>
                                        @endif
                                    </td>
                                    <td>{{ $article->published_at?->locale('id')->translatedFormat('d M Y') ?? '—' }}</td>
                                    <td>
                                        <div class="dropdown table-action-dropdown">
                                            <button class="btn-custom btn-custom-outline-secondary btn-custom-sm dropdown-toggle table-action-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Aksi artikel: {{ $article->title }}">
                                                <i class="bi bi-three-dots-vertical" aria-hidden="true"></i> Aksi
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                                                <a class="dropdown-item" href="{{ route('admin.articles.edit', $article) }}">
                                                    <i class="bi bi-pencil" aria-hidden="true"></i> Ubah
                                                </a>
                                                @if ($article->published_at && $article->published_at->lte(now()))
                                                    <a class="dropdown-item" href="{{ route('articles.show', $article) }}" target="_blank" rel="noopener">
                                                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Lihat
                                                    </a>
                                                @endif
                                                <div class="dropdown-divider"></div>
                                                <form method="post" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Hapus artikel ini secara permanen?')">
                                                    @csrf
                                                    @method('delete')
                                                    <button class="dropdown-item text-danger" type="submit">
                                                        <i class="bi bi-trash" aria-hidden="true"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pagination-wrap">{{ $articles->links() }}</div>
            @else
                <div class="empty-state">
                    <i class="bi bi-file-earmark-text empty-icon" aria-hidden="true"></i>
                    <h3>{{ $search ? 'Artikel tidak ditemukan' : 'Belum ada artikel' }}</h3>
                    <p>{{ $search ? 'Coba kata kunci yang berbeda.' : 'Mulai tulis artikel untuk dibagikan di halaman publik.' }}</p>
                    @if ($search)
                        <a href="{{ route('admin.articles.index') }}" class="btn-custom btn-custom-outline-secondary">Lihat semua artikel</a>
                    @else
                        <a href="{{ route('admin.articles.create') }}" class="btn-custom btn-custom-primary">Tulis artikel pertama</a>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
