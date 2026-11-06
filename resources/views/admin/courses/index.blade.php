@extends('layouts.admin')

@section('title', 'Kelola Kelas')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Kelola kelas</h1>
            <p class="page-subtitle">Atur informasi kelas yang tersedia di RuangKelas.</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.courses.create') }}" class="btn-custom btn-custom-primary">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah kelas
            </a>
        </div>
    </div>

    <section class="card admin-panel">
        <div class="card-header">
            <div>
                <h2 class="card-title">Daftar kelas</h2>
                <p class="page-subtitle">Cari dan kelola kelas yang ditampilkan kepada pengunjung.</p>
            </div>
        </div>
        <div class="admin-panel-body">
            <form class="admin-search" action="{{ route('admin.courses.index') }}" method="get">
                <input class="form-control-custom" type="search" name="search" value="{{ $search }}" placeholder="Cari kelas, instruktur, kategori..." aria-label="Cari kelas">
                <button class="btn-custom btn-custom-primary" type="submit"><i class="bi bi-search" aria-hidden="true"></i> Cari</button>
                @if ($search)
                    <a class="btn-custom btn-custom-outline-secondary" href="{{ route('admin.courses.index') }}">Reset</a>
                @endif
            </form>

            @if ($courses->isNotEmpty())
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr><th>Kelas</th><th>Kategori</th><th>Instruktur</th><th>Harga</th><th>Status</th><th>Pendaftar</th><th>Ekspor</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($courses as $course)
                                <tr>
                                    <td><span class="table-strong">{{ $course->title }}</span><small class="table-subtitle">{{ $course->created_at->format('d M Y') }}</small></td>
                                    <td>{{ $course->category->name }}</td>
                                    <td>{{ $course->instructor }}</td>
                                    <td>{{ (float) $course->price === 0.0 ? 'Gratis' : 'Rp '.number_format((float) $course->price, 0, ',', '.') }}</td>
                                    <td><span class="status-badge status-{{ $course->status->value }}">{{ $course->status->label() }}</span></td>
                                    <td><a class="text-link" href="{{ route('admin.enrollments.index', $course) }}">{{ $course->enrollments_count }}</a></td>
                                    <td><a class="btn-custom btn-custom-outline-secondary btn-custom-sm" href="{{ route('admin.enrollments.export', $course) }}">↓ CSV</a></td>
                                    <td>
                                        <div class="dropdown table-action-dropdown">
                                            <button class="btn-custom btn-custom-outline-secondary btn-custom-sm dropdown-toggle table-action-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Aksi kelas: {{ $course->title }}">
                                                <i class="bi bi-three-dots-vertical" aria-hidden="true"></i> Aksi
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                                                <a class="dropdown-item" href="{{ route('admin.courses.edit', $course) }}">
                                                    <i class="bi bi-pencil" aria-hidden="true"></i> Ubah
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <form method="post" action="{{ route('admin.courses.destroy', $course) }}" onsubmit="return confirm('Hapus kelas ini beserta semua pendaftar?')">
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
                <div class="pagination-wrap">{{ $courses->links() }}</div>
            @else
                <div class="empty-state">
                    <i class="bi bi-journal-x empty-icon" aria-hidden="true"></i>
                    <h3>Belum ada kelas</h3>
                    <p>Tambahkan kelas pertama Anda untuk mulai mengisi katalog.</p>
                    <a href="{{ route('admin.courses.create') }}" class="btn-custom btn-custom-primary">Tambah kelas</a>
                </div>
            @endif
        </div>
    </section>
@endsection
