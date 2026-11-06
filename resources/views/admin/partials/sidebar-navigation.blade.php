<div class="flex-grow-1 overflow-y-auto">
    <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">MENU UTAMA</div>
        <ul class="sidebar-menu-list">
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.dashboard') }}" @class(['sidebar-menu-link', 'active' => request()->routeIs('admin.dashboard')]) @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                    <i class="bi bi-grid-fill" aria-hidden="true"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.courses.index') }}" @class(['sidebar-menu-link', 'active' => request()->routeIs('admin.courses.*')]) @if (request()->routeIs('admin.courses.*')) aria-current="page" @endif>
                    <i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i>
                    <span>Kelas</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.enrollments.all') }}" @class(['sidebar-menu-link', 'active' => request()->routeIs('admin.enrollments.*')]) @if (request()->routeIs('admin.enrollments.*')) aria-current="page" @endif>
                    <i class="bi bi-people-fill" aria-hidden="true"></i>
                    <span>Pendaftar</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.categories.index') }}" @class(['sidebar-menu-link', 'active' => request()->routeIs('admin.categories.*')]) @if (request()->routeIs('admin.categories.*')) aria-current="page" @endif>
                    <i class="bi bi-collection-fill" aria-hidden="true"></i>
                    <span>Kategori</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.articles.index') }}" @class(['sidebar-menu-link', 'active' => request()->routeIs('admin.articles.*')]) @if (request()->routeIs('admin.articles.*')) aria-current="page" @endif>
                    <i class="bi bi-file-earmark-text-fill" aria-hidden="true"></i>
                    <span>Artikel</span>
                </a>
            </li>
        </ul>
    </div>
</div>
