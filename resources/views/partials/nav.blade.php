@php
    $links = [
        ['dashboard', '🏕️', 'Dashboard'],
        ['home', '🦒', 'Jelajah'],
        ['peta', '🗺️', 'Peta'],
        ['tiket.create', '🎟️', 'Pesan Tiket'],
        ['tiket.riwayat', '📋', 'Riwayat'],
        ['statistik', '📊', 'Statistik'],
    ];
@endphp
<nav class="zoo-nav">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
        <a href="{{ route('dashboard') }}" class="zoo-logo" aria-label="Safari Zoo">
            <span class="cube" aria-hidden="true"><span>🦁</span><span>🐘</span><span>🦒</span><span>🐼</span></span>
            <span class="brand">Safari Zoo<small>Kebun Binatang</small></span>
        </a>

        <div class="hidden xl:flex items-center gap-1">
            @foreach ($links as [$route, $icon, $label])
                <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($route) ? 'active' : '' }}">{{ $icon }} {{ $label }}</a>
            @endforeach
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="nav-link">🛠️ Admin</a>
            @endif
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('profil') }}" class="hidden sm:flex items-center gap-2 nav-link {{ request()->routeIs('profil') ? 'active' : '' }}" title="Profil">
                @if (auth()->user()->profile_picture)
                    <img src="{{ auth()->user()->profilePictureUrl() }}" alt="" class="w-8 h-8 rounded-full object-cover border-2 border-yellow-300">
                @else
                    <span class="w-8 h-8 rounded-full bg-yellow-300 text-green-900 flex items-center justify-center font-bold">{{ strtoupper(substr(auth()->user()->username, 0, 1)) }}</span>
                @endif
                <span class="truncate" style="max-width: 8rem">{{ auth()->user()->username }}</span>
            </a>
            <a href="{{ route('logout') }}" class="nav-link" title="Keluar">🚪 <span class="hidden md:inline">Keluar</span></a>
            <button type="button" class="xl:hidden nav-link text-xl" data-menu-toggle aria-expanded="false" aria-label="Menu">☰</button>
        </div>
    </div>
    <div class="mobile-menu xl:hidden">
        <div class="max-w-7xl mx-auto px-4 pb-4 grid grid-cols-2 gap-2">
            @foreach ($links as [$route, $icon, $label])
                <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($route) ? 'active' : '' }}">{{ $icon }} {{ $label }}</a>
            @endforeach
            <a href="{{ route('profil') }}" class="nav-link">👤 Profil</a>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="nav-link">🛠️ Admin</a>
            @endif
        </div>
    </div>
</nav>
