@extends('layouts.admin')

@section('title', 'Semua Pendaftar')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Semua pendaftar</h1>
            <p class="page-subtitle">Daftar peserta dari semua kelas yang tersedia.</p>
        </div>
        <div class="page-header-actions">
            <a class="btn-custom btn-custom-outline-primary" href="{{ route('admin.enrollments.export-all') }}">
                <i class="bi bi-download" aria-hidden="true"></i> Ekspor CSV
            </a>
        </div>
    </div>

    <section class="card admin-panel">
        <div class="card-header">
            <div>
                <h2 class="card-title">Data pendaftar</h2>
                <p class="page-subtitle">Kelola status atau hapus pendaftar dari kelas terkait.</p>
            </div>
        </div>
        <div class="admin-panel-body">
            <form class="admin-search" action="{{ route('admin.enrollments.all') }}" method="get">
                <label class="search-field">
                    <input class="form-control-custom" type="search" name="search" value="{{ $search }}" placeholder="Cari nama, email, telepon, atau kelas..." aria-label="Cari pendaftar">
                </label>
                <button class="btn-custom btn-custom-primary" type="submit">
                    <i class="bi bi-search" aria-hidden="true"></i> Cari
                </button>
                @if ($search)
                    <a class="btn-custom btn-custom-outline-secondary" href="{{ route('admin.enrollments.all') }}">Reset</a>
                @endif
            </form>

            @if ($enrollments->isNotEmpty())
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr><th>Nama</th><th>Kelas</th><th>Email</th><th>Nomor telepon</th><th>Tanggal daftar</th><th>Status</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($enrollments as $enrollment)
                                <tr>
                                    <td class="table-strong">{{ $enrollment->name }}</td>
                                    <td><a class="text-link" href="{{ route('admin.enrollments.index', $enrollment->course) }}">{{ $enrollment->course->title }}</a></td>
                                    <td>{{ $enrollment->email }}</td>
                                    <td>{{ $enrollment->phone }}</td>
                                    <td>{{ $enrollment->created_at->format('d M Y, H:i') }}</td>
                                    <td><span class="status-badge status-{{ $enrollment->status->value }}">{{ $enrollment->status->label() }}</span></td>
                                    <td>
                                        <div class="dropdown table-action-dropdown">
                                            <button class="btn-custom btn-custom-outline-secondary btn-custom-sm dropdown-toggle table-action-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Aksi pendaftar: {{ $enrollment->name }}">
                                                <i class="bi bi-three-dots-vertical" aria-hidden="true"></i> Aksi
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                                                <form method="post" action="{{ route('admin.enrollments.update', [$enrollment->course, $enrollment]) }}">
                                                    @csrf
                                                    @method('patch')
                                                    <button class="dropdown-item" type="submit" name="status" value="accepted">
                                                        <i class="bi bi-check-circle" aria-hidden="true"></i> Terima
                                                    </button>
                                                    <button class="dropdown-item" type="submit" name="status" value="rejected">
                                                        <i class="bi bi-x-circle" aria-hidden="true"></i> Tolak
                                                    </button>
                                                </form>
                                                <div class="dropdown-divider"></div>
                                                <form method="post" action="{{ route('admin.enrollments.destroy', [$enrollment->course, $enrollment]) }}" onsubmit="return confirm('Hapus peserta ini secara permanen? Tindakan ini tidak dapat dibatalkan.')">
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
                <div class="pagination-wrap">{{ $enrollments->links() }}</div>
            @else
                <div class="empty-state">
                    <i class="bi bi-people empty-icon" aria-hidden="true"></i>
                    <h3>{{ $search ? 'Pendaftar tidak ditemukan' : 'Belum ada pendaftar' }}</h3>
                    <p>{{ $search ? 'Coba kata kunci yang berbeda.' : 'Pendaftaran dari semua kelas akan muncul di sini.' }}</p>
                    @if ($search)
                        <a href="{{ route('admin.enrollments.all') }}" class="btn-custom btn-custom-outline-secondary">Lihat semua pendaftar</a>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
