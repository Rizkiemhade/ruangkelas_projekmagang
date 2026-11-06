@extends('layouts.app')

@section('title', $course->title)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($course->description), 155))

@section('content')
    <section class="detail-section">
        <div class="container">
            <a class="back-link" href="{{ route('home') }}#kelas">← Kembali ke kelas</a>
            <div class="detail-grid">
                <article class="detail-main">
                    <div class="detail-cover" @if ($course->image_path) style="background-image:url('{{ Storage::disk('public')->url($course->image_path) }}')" @endif>
                        @unless ($course->image_path)<span aria-hidden="true">✳</span>@endunless
                        <span class="course-category">{{ $course->category->name }}</span>
                    </div>
                    <div class="detail-copy">
                        <span class="eyebrow eyebrow-dark">KELAS ONLINE · {{ $course->category->name }}</span>
                        <h1>{{ $course->title }}</h1>
                        <p class="detail-instructor">Dibimbing oleh <strong>{{ $course->instructor }}</strong></p>
                        <div class="prose">{{ $course->description }}</div>
                    </div>
                </article>
                <aside class="enroll-card">
                    <span class="eyebrow eyebrow-dark">MULAI BELAJAR</span>
                    <div class="detail-price">{{ (float) $course->price === 0.0 ? 'Gratis' : 'Rp '.number_format((float) $course->price, 0, ',', '.') }}</div>
                    <p>Isi data berikut untuk mendaftar ke kelas ini.</p>
                    <form method="post" action="{{ route('enrollments.store', $course) }}" class="stack-form">
                        @csrf
                        <label>Nama lengkap<input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="150"></label>
                        @error('name')<small class="field-error">{{ $message }}</small>@enderror
                        <label>Email<input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required maxlength="255"></label>
                        @error('email')<small class="field-error">{{ $message }}</small>@enderror
                        <label>Nomor telepon<input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" required maxlength="30" placeholder="+62 812 3456 7890"></label>
                        @error('phone')<small class="field-error">{{ $message }}</small>@enderror
                        <button class="button button-primary button-full" type="submit">Daftar kelas <span aria-hidden="true">↗</span></button>
                    </form>
                    <p class="privacy-note">Data Anda hanya digunakan untuk keperluan pendaftaran kelas.</p>
                </aside>
            </div>
        </div>
    </section>
@endsection
