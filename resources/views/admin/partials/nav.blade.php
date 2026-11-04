@php
    $adminLinks = [
        ['admin.dashboard', '🏕️', 'Dashboard'],
        ['admin.analytics', '📈', 'Analytics'],
        ['admin.orders', '🎟️', 'Tiket'],
        ['admin.orders.bulk-page', '🧺', 'Aksi Massal'],
        ['admin.settings', '⚙️', 'Pengaturan'],
    ];
@endphp
<nav class="zoo-nav">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
        <a href="{{ route('admin.dashboard') }}" class="zoo-logo" aria-label="Panel Admin">
            <span class="cube" aria-hidden="true"><span>🦁</span><span>🐘</span><span>🦒</span><span>🐼</span></span>
            <span class="brand">Safari Admin<small>Ranger Control</small></span>
        </a>

        <div class="hidden xl:flex items-center gap-1">
            @foreach ($adminLinks as [$route, $icon, $label])
                <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($route) ? 'active' : '' }}">{{ $icon }} {{ $label }}</a>
            @endforeach
            <a href="{{ route('dashboard') }}" class="nav-link">🌳 Situs Zoo</a>
        </div>

        <div class="flex items-center gap-2">
            <span class="hidden sm:inline nav-link" style="cursor: default">🧢 {{ auth()->user()->username }}</span>
            <a href="{{ route('logout') }}" class="nav-link">🚪 <span class="hidden md:inline">Keluar</span></a>
            <button type="button" class="xl:hidden nav-link text-xl" data-menu-toggle aria-expanded="false" aria-label="Menu">☰</button>
        </div>
    </div>
    <div class="mobile-menu xl:hidden">
        <div class="max-w-7xl mx-auto px-4 pb-4 grid grid-cols-2 gap-2">
            @foreach ($adminLinks as [$route, $icon, $label])
                <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($route) ? 'active' : '' }}">{{ $icon }} {{ $label }}</a>
            @endforeach
            <a href="{{ route('dashboard') }}" class="nav-link">🌳 Situs Zoo</a>
        </div>
    </div>
</nav>
