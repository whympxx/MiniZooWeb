<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.csrf')
    <title>@yield('title', 'Kebun Binatang Safari') · Safari Zoo</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🦁</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/zoo-theme.css') }}?v=2" rel="stylesheet">
    @stack('head')
</head>
<body class="zoo-body flex flex-col">
    @include('partials.scenery')

    @unless (View::hasSection('no-nav'))
        @auth
            @include('partials.nav')
        @endauth
    @endunless

    <main class="flex-1 w-full">
        @if (session('success') || session('status'))
            <div class="max-w-6xl mx-auto px-4 pt-6">
                <div class="zoo-alert success pop-in">✅ {{ session('success') ?? session('status') }}</div>
            </div>
        @endif
        @if (session('error'))
            <div class="max-w-6xl mx-auto px-4 pt-6">
                <div class="zoo-alert error">⚠️ {{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    @unless (View::hasSection('no-nav'))
        <footer class="zoo-footer mt-16">
            <div class="max-w-6xl mx-auto px-4 py-8 grid gap-6 md:grid-cols-3 text-sm">
                <div>
                    <div class="font-display text-xl font-bold text-white mb-1">🦁 Safari Zoo</div>
                    <p class="opacity-80">Petualangan, edukasi satwa, dan wisata alam untuk seluruh keluarga.</p>
                </div>
                <div>
                    <div class="font-bold text-white mb-1">🕗 Jam Buka</div>
                    @php $sys = \App\Support\Settings::get('system_settings'); @endphp
                    <p class="opacity-80">Setiap hari, {{ $sys['zoo_opening_hours'] }} – {{ $sys['zoo_closing_hours'] }} WIB</p>
                </div>
                <div class="md:text-right">
                    <div class="font-bold text-white mb-1">🌿 Jaga Alam Kita</div>
                    <p class="opacity-80">&copy; {{ date('Y') }} Kebun Binatang Safari</p>
                </div>
            </div>
        </footer>
    @endunless

    <script src="{{ asset('assets/js/zoo-3d.js') }}?v=2" defer></script>
    @stack('scripts')
</body>
</html>
