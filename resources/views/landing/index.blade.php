@extends('layouts.app')

@section('title', 'Kelas Online untuk Skill Masa Depan')
@section('meta_description', 'Temukan kelas teknologi praktis bersama instruktur berpengalaman di RuangKelas.')

@section('content')
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-copy">
                <span class="eyebrow"><span class="eyebrow-dot"></span> RUANG BELAJAR DIGITAL</span>
                <h1>Skill baru.<br><span>Masa depan baru.</span></h1>
                <p>Belajar teknologi dengan kelas praktis yang dirancang untuk membantumu berkembang, satu langkah setiap hari.</p>
                <a class="button button-light" href="#kelas">Temukan kelas <span aria-hidden="true">↗</span></a>
                <div class="hero-proof"><span class="proof-avatars">● ● ●</span><span>Belajar dengan komunitas yang suportif</span></div>
            </div>
            <div class="hero-art" aria-hidden="true">
                <div class="orbit orbit-one"></div><div class="orbit orbit-two"></div>
                <div class="art-card art-card-main"><span class="art-icon">✳</span><span>Belajar dengan<br>cara yang nyata.</span><span class="art-line"></span></div>
                <div class="art-note"><span>✦</span> Mulai dari sini</div>
                <div class="art-spark">✳</div>
            </div>
        </div>
    </section>

    <section class="section section-courses" id="kelas">
        <div class="container">
            <div class="section-heading">
                <div><span class="eyebrow eyebrow-dark">PILIH JALURMU</span><h2>Kelas pilihan untukmu</h2><p>Materi relevan, mentor berpengalaman, dan praktik yang bisa langsung diterapkan.</p></div>
                <span class="course-total">{{ $courses->total() }} kelas tersedia</span>
            </div>
            <form class="filter-bar" action="{{ route('home') }}#kelas" method="get">
                <label class="search-field"><span aria-hidden="true">⌕</span><input type="search" name="search" value="{{ $search }}" placeholder="Cari kelas, instruktur, atau topik..." aria-label="Cari kelas"></label>
                <select name="category" aria-label="Filter kategori">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected($selectedCategory === $category->slug)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <button class="button button-primary" type="submit">Cari kelas</button>
                @if ($search || $selectedCategory)
                    <a class="button button-quiet" href="{{ route('home') }}#kelas">Reset</a>
                @endif
            </form>

            @if ($courses->isNotEmpty())
                <div class="course-grid">
                    @foreach ($courses as $course)
                        @include('landing._course-card', ['course' => $course])
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $courses->links() }}</div>
            @else
                <div class="empty-state"><span class="empty-icon">⌕</span><h3>Kelas belum ditemukan</h3><p>Coba kata kunci atau kategori lain.</p><a href="{{ route('home') }}#kelas" class="button button-outline">Lihat semua kelas</a></div>
            @endif
        </div>
    </section>

    <section class="about-band" id="tentang">
        <div class="container about-grid">
            <span class="about-symbol" aria-hidden="true">✳</span>
            <div><span class="eyebrow eyebrow-dark">BELAJAR YANG BERARTI</span><h2>Mulai dari penasaran,<br>bertumbuh dengan percaya diri.</h2></div>
            <p>RuangKelas menghadirkan pembelajaran digital yang mudah diakses, terarah, dan dekat dengan kebutuhan dunia kerja.</p>
        </div>
    </section>

    <section class="article-callout">
        <div class="container article-callout-inner">
            <div><span class="eyebrow eyebrow-dark">RUANG UNTUK IDE BARU</span><h2>Baca, coba, lalu bertumbuh.</h2><p>Temukan panduan belajar dan cerita dari dunia teknologi.</p></div>
            <a class="button button-outline" href="{{ route('articles.index') }}">Jelajahi artikel <span aria-hidden="true">→</span></a>
        </div>
    </section>

    <section class="contact-section" id="kontak">
        <div class="container contact-inner">
            <div><span class="eyebrow eyebrow-dark">ADA YANG INGIN DITANYAKAN?</span><h2>Kami siap membantu.</h2></div>
            <a class="button button-primary" href="mailto:halo@ruangkelas.id">halo@ruangkelas.id <span aria-hidden="true">↗</span></a>
        </div>
    </section>
@endsection
