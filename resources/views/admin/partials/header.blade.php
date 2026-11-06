<header class="navbar-custom">
    <div class="navbar-left">
        <button class="btn-desktop-toggle d-none d-xl-flex align-items-center justify-content-center"
            id="desktop-sidebar-toggle" aria-label="Ciutkan navigasi">
            <i class="bi bi-chevron-bar-left" aria-hidden="true"></i>
        </button>
        <button class="sidebar-toggle-btn" id="sidebar-toggle" aria-label="Buka navigasi">
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>
    </div>

    <div class="navbar-search-wrapper admin-context">
        <i class="bi bi-mortarboard" aria-hidden="true"></i>
        <span>Panel Administrasi RuangKelas</span>
    </div>

    <div class="navbar-actions">
        <a class="navbar-action-btn admin-home-link" href="{{ route('home') }}" aria-label="Lihat situs">
            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
        </a>
        <button class="navbar-action-btn me-1" aria-label="Mode layar penuh" id="btn-fullscreen" type="button">
            <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
        </button>
        <div class="dropdown">
            <button class="navbar-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
                aria-expanded="false" id="profile-dropdown">
                <img
                    src="{{ auth()->user()->profile_photo_path ? Storage::disk('public')->url(auth()->user()->profile_photo_path) : asset('assets/images/avatar.png') }}"
                    alt=""
                    class="navbar-profile-img"
                >
                <span class="navbar-profile-name d-none d-md-inline">{{ auth()->user()->name }}</span>
                <i class="bi bi-chevron-down navbar-profile-caret" aria-hidden="true"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile" aria-labelledby="profile-dropdown">
                <li><span class="dropdown-header">{{ auth()->user()->email }}</span></li>
                <li><a class="dropdown-item" href="{{ route('admin.profile.edit') }}"><i class="bi bi-person-gear"></i> Edit profil</a></li>
                <li><a class="dropdown-item" href="{{ route('home') }}"><i class="bi bi-house"></i> Lihat situs</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="post" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right"></i> Keluar</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
