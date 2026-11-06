<aside class="sidebar-wrapper" id="sidebar">
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
        <i class="bi bi-asterisk" aria-hidden="true"></i>
        <span>RuangKelas</span>
    </a>

    @include('admin.partials.sidebar-navigation')

    <div class="sidebar-profile">
        <img
            src="{{ auth()->user()->profile_photo_path ? Storage::disk('public')->url(auth()->user()->profile_photo_path) : asset('assets/images/avatar.png') }}"
            alt=""
            class="sidebar-profile-img"
        >
        <div class="sidebar-profile-info">
            <div class="sidebar-profile-name">{{ auth()->user()->name }}</div>
            <div class="sidebar-profile-email">{{ auth()->user()->email }}</div>
        </div>
    </div>
</aside>
