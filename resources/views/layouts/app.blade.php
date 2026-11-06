<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RuangKelas') · RuangKelas</title>
    <meta name="description" content="@yield('meta_description', 'Kelas online praktis untuk membangun keterampilan digital.')">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="container nav-wrap">
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark">r.</span> ruangkelas</a>
            <nav class="main-nav" aria-label="Navigasi utama">
                <a href="{{ route('home') }}#kelas">Jelajahi kelas</a>
                <a href="{{ route('articles.index') }}">Artikel</a>
                <a href="{{ route('home') }}#tentang">Tentang</a>
                <a href="{{ route('home') }}#kontak">Kontak</a>
                @auth
                    <a class="nav-admin" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <form method="post" action="{{ route('admin.logout') }}" class="nav-logout">
                        @csrf
                        <button class="link-button" type="submit">Keluar</button>
                    </form>
                @else
                    <a class="nav-admin" href="{{ route('login') }}">Admin</a>
                @endauth
            </nav>
        </div>
    </header>

    @if (session('success') || $errors->any())
        <div class="container notices">
            @if (session('success'))
                <div class="notice notice-success" role="status">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice notice-error" role="alert">
                    <strong>Mohon periksa kembali:</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <a class="brand brand-footer" href="{{ route('home') }}"><span class="brand-mark">r.</span> ruangkelas</a>
            <p>Belajar hari ini, bertumbuh setiap hari.</p>
            <span>© {{ date('Y') }} RuangKelas</span>
        </div>
    </footer>
</body>
</html>
