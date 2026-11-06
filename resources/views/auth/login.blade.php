@extends('layouts.app')

@section('title', 'Login Admin')

@section('content')
    <section class="login-section">
        <div class="login-card">
            <span class="brand-mark brand-mark-large">r.</span>
            <span class="eyebrow eyebrow-dark">AREA PENGELOLA</span>
            <h1>Selamat datang kembali.</h1>
            <p>Masuk untuk mengelola kelas dan pendaftar RuangKelas.</p>
            <form action="{{ route('login.store') }}" method="post" class="stack-form">
                @csrf
                <label>Email admin<input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus></label>
                <label>Kata sandi<input type="password" name="password" autocomplete="current-password" required></label>
                <label class="check-label"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
                <button class="button button-primary button-full" type="submit">Masuk ke dashboard <span aria-hidden="true">→</span></button>
            </form>
        </div>
    </section>
@endsection
