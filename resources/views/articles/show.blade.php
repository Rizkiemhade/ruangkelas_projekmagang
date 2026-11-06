@extends('layouts.app')

@section('title', $article->title)
@section('meta_description', $article->excerpt)

@section('content')
    <section class="article-detail-section">
        <div class="container article-detail-container">
            <a class="back-link" href="{{ route('articles.index') }}">← Kembali ke artikel</a>
            <article class="article-detail">
                <header class="article-detail-header">
                    <span class="eyebrow eyebrow-dark">CATATAN RUANGKELAS</span>
                    <h1>{{ $article->title }}</h1>
                    <p class="article-byline">Oleh <strong>{{ $article->author_name }}</strong> · {{ $article->published_at->locale('id')->translatedFormat('d F Y') }}</p>
                    <p class="article-excerpt">{{ $article->excerpt }}</p>
                </header>
                <div class="article-detail-art" aria-hidden="true" @if ($article->thumbnail_path) style="background-image: url('{{ Storage::disk('public')->url($article->thumbnail_path) }}'); background-size: cover; background-position: center" @endif>
                    @unless ($article->thumbnail_path)<span>✳</span>@endunless
                </div>
                <div class="article-body">{{ $article->body }}</div>
            </article>
            <div class="article-detail-footer"><a class="button button-outline" href="{{ route('articles.index') }}">← Jelajahi artikel lain</a></div>
        </div>
    </section>
@endsection
