@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Selamat datang, {{ auth()->user()->name }}. Berikut ringkasan RuangKelas.</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <article class="card card-stat h-100 mb-0">
                <div>
                    <div class="card-header"><span class="stat-label">Total kelas</span><i class="bi bi-journal-bookmark text-muted" aria-hidden="true"></i></div>
                    <div class="stat-value">{{ number_format($courseCount) }}</div>
                    <a class="trend-badge trend-up" href="{{ route('admin.courses.index') }}">Kelola kelas <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                </div>
            </article>
        </div>
        <div class="col-sm-6 col-xl-3">
            <article class="card card-stat h-100 mb-0">
                <div>
                    <div class="card-header"><span class="stat-label">Total pendaftar</span><i class="bi bi-people text-muted" aria-hidden="true"></i></div>
                    <div class="stat-value">{{ number_format($enrollmentCount) }}</div>
                    <a class="trend-badge trend-up" href="{{ route('admin.enrollments.all') }}">Lihat pendaftar <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                </div>
            </article>
        </div>
        <div class="col-sm-6 col-xl-3">
            <article class="card card-stat h-100 mb-0">
                <div>
                    <div class="card-header"><span class="stat-label">Menunggu ditinjau</span><i class="bi bi-hourglass-split text-muted" aria-hidden="true"></i></div>
                    <div class="stat-value">{{ number_format($pendingEnrollmentCount) }}</div>
                    <a class="trend-badge" href="{{ route('admin.enrollments.all') }}">Tinjau pendaftar</a>
                </div>
            </article>
        </div>
        <div class="col-sm-6 col-xl-3">
            <article class="card card-stat h-100 mb-0">
                <div>
                    <div class="card-header"><span class="stat-label">Total kategori</span><i class="bi bi-collection text-muted" aria-hidden="true"></i></div>
                    <div class="stat-value">{{ number_format($categoryCount) }}</div>
                    <a class="trend-badge trend-up" href="{{ route('admin.categories.index') }}">Kelola kategori <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                </div>
            </article>
        </div>
    </div>

    <section class="card admin-panel" id="pendaftar-terbaru">
        <div class="card-header">
            <div>
                <h2 class="card-title">Pendaftar terbaru</h2>
                
            </div>
            <a href="{{ route('admin.enrollments.all') }}" class="btn-custom btn-custom-outline-primary btn-custom-sm">Semua pendaftar</a>
        </div>
        <div class="admin-panel-body">
            @if ($latestEnrollments->isNotEmpty())
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr><th>Nama</th><th>Kelas</th><th>Email</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($latestEnrollments as $enrollment)
                                <tr>
                                    <td class="table-strong">{{ $enrollment->name }}</td>
                                    <td>{{ $enrollment->course->title }}</td>
                                    <td>{{ $enrollment->email }}</td>
                                    <td>{{ $enrollment->created_at->format('d M Y') }}</td>
                                    <td><span class="status-badge status-{{ $enrollment->status->value }}">{{ $enrollment->status->label() }}</span></td>
                                    <td><a class="text-link" href="{{ route('admin.enrollments.all') }}">Buka <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-inline">Belum ada pendaftar. Pendaftaran kelas akan muncul di sini.</div>
            @endif
        </div>
    </section>
@endsection
