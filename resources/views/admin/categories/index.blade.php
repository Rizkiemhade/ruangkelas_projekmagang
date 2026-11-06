@extends('layouts.admin')

@section('title', 'Kelola Kategori')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Kategori</h1>
            <p class="page-subtitle">Kelompokkan kelas agar pengunjung lebih mudah menemukannya.</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.categories.create') }}" class="btn-custom btn-custom-primary">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah kategori
            </a>
        </div>
    </div>

    <section class="card admin-panel">
        <div class="card-header">
            <div>
                <h2 class="card-title">Daftar kategori</h2>
                <p class="page-subtitle">Kelola pengelompokan topik kelas.</p>
            </div>
        </div>
        <div class="admin-panel-body">
            @if ($categories->isNotEmpty())
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr><th>Nama kategori</th><th>Deskripsi</th><th>Jumlah kelas</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td class="table-strong">{{ $category->name }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($category->description, 90) }}</td>
                                    <td>{{ $category->courses_count }}</td>
                                    <td>
                                        <div class="dropdown table-action-dropdown">
                                            <button class="btn-custom btn-custom-outline-secondary btn-custom-sm dropdown-toggle table-action-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Aksi kategori: {{ $category->name }}">
                                                <i class="bi bi-three-dots-vertical" aria-hidden="true"></i> Aksi
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                                                <a class="dropdown-item" href="{{ route('admin.categories.edit', $category) }}">
                                                    <i class="bi bi-pencil" aria-hidden="true"></i> Ubah
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <form method="post" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">
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
                <div class="pagination-wrap">{{ $categories->links() }}</div>
            @else
                <div class="empty-state">
                    <i class="bi bi-collection empty-icon" aria-hidden="true"></i>
                    <h3>Belum ada kategori</h3>
                    <p>Buat kategori untuk mengelompokkan kelas.</p>
                    <a href="{{ route('admin.categories.create') }}" class="btn-custom btn-custom-primary">Tambah kategori</a>
                </div>
            @endif
        </div>
    </section>
@endsection
