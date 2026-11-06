@extends('layouts.app')

@section('title', 'Artikel')
@section('meta_description', 'Catatan dan panduan singkat seputar belajar teknologi dan membangun karier digital.')

@section('content')
    <section class="article-hero">
        <div class="container">
            <span class="eyebrow"><span class="eyebrow-dot"></span> CATATAN RUANGKELAS</span>
            <h1>Ide baik untuk<br><span>langkah berikutnya.</span></h1>
            <p>Temukan panduan singkat untuk belajar teknologi, membangun proyek, dan mengembangkan karier digital.</p>
        </div>
    </section>

    <section class="section article-section">
        <div class="container">
            <div class="section-heading">
                <div><span class="eyebrow eyebrow-dark">BACA & BERTUMBUH</span><h2>Artikel terbaru</h2><p>Inspirasi praktis dari tim dan instruktur RuangKelas.</p></div>
                <span class="course-total">{{ $articles->total() }} artikel</span>
            </div>
            <form class="filter-bar article-search" action="{{ route('articles.index') }}" method="get">
                <label class="search-field"><span aria-hidden="true">⌕</span><input type="search" name="search" value="{{ $search }}" placeholder="Cari artikel..." aria-label="Cari artikel"></label>
                <button class="button button-primary" type="submit">Cari artikel</button>
                @if ($search)<a class="button button-quiet" href="{{ route('articles.index') }}">Reset</a>@endif
            </form>

            @if ($articles->isNotEmpty())
                <div class="article-grid">
                    @foreach ($articles as $article)
                        <article class="article-card">
                            <a class="article-card-art" href="{{ route('articles.show', $article) }}" aria-label="Baca {{ $article->title }}" @if ($article->thumbnail_path) style="background-image: url('{{ Storage::disk('public')->url($article->thumbnail_path) }}'); background-size: cover; background-position: center" @endif>
                                @unless ($article->thumbnail_path)<span aria-hidden="true">✳</span>@endunless
                                <span class="article-card-label">RUANG IDE</span>
                            </a>
                            <div class="article-card-body">
                                <span class="article-date">{{ $article->published_at->locale('id')->translatedFormat('d F Y') }} · {{ $article->author_name }}</span>
                                <h2><a href="{{ route('articles.show', $article) }}">{{ $article->title }}</a></h2>
                                <p>{{ $article->excerpt }}</p>
                                <a class="article-read-link" href="{{ route('articles.show', $article) }}">Baca artikel <span aria-hidden="true">→</span></a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $articles->links() }}</div>
            @else
                <div class="empty-state"><span class="empty-icon">⌕</span><h3>Artikel belum ditemukan</h3><p>Coba kata kunci yang berbeda.</p><a href="{{ route('articles.index') }}" class="button button-outline">Lihat semua artikel</a></div>
            @endif
        </div>
    </section>
@endsection
