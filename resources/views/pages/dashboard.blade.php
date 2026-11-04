@extends('layouts.zoo')

@section('title', 'Dashboard')
@include('partials.three')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
    $menus = [
        ['tiket.create', '🎟️', 'Pesan Tiket', 'Amankan tiket kunjunganmu', 'from-green-400 to-green-600'],
        ['tiket.riwayat', '📋', 'Riwayat Tiket', 'Lihat & cetak e-tiket', 'from-yellow-300 to-orange-500'],
        ['home', '🦒', 'Jelajah Satwa', 'Kenali penghuni Safari', 'from-teal-300 to-green-600'],
        ['peta', '🗺️', 'Peta Zona', 'Rencanakan rute jalanmu', 'from-blue-300 to-blue-600'],
        ['statistik', '📊', 'Statistik', 'Data pengunjung & koleksi', 'from-purple-300 to-indigo-500'],
        ['profil', '👤', 'Profil', 'Atur akun dan foto', 'from-pink-300 to-red-500'],
    ];
@endphp

<div class="max-w-6xl mx-auto px-4 pt-8">
    {{-- Hero dengan pulau 3D --}}
    <section class="hero-3d reveal" data-zoo-scene style="min-height: 420px;">
        <div class="hero-content p-6 sm:p-10 max-w-md">
            <span class="wood-sign">{{ $greeting }} ☀️</span>
            <h1 class="mt-5 text-3xl sm:text-5xl font-bold text-green-900 leading-tight">Hai, {{ $user->username }}!</h1>
            <p class="mt-3 text-green-800 font-semibold">Para satwa sudah menunggu. Mau ke mana kita hari ini?</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('tiket.create') }}" class="btn-zoo">🎟️ Pesan Tiket</a>
                <a href="{{ route('home') }}" class="btn-zoo ghost">🦒 Jelajah</a>
            </div>
        </div>
    </section>

    {{-- Statistik tiket --}}
    <section class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mt-8">
        @foreach ([
            ['📅', 'Member Sejak', $user->created_at->translatedFormat('M Y'), null],
            ['🎟️', 'Total Tiket', null, $ticket_stats['total_tickets']],
            ['✅', 'Tiket Lunas', null, $ticket_stats['paid_orders']],
            ['⏳', 'Menunggu Bayar', null, $ticket_stats['pending_orders']],
        ] as $i => [$icon, $label, $text, $count])
            <div class="zoo-card stat-3d reveal" data-delay="{{ $i + 1 }}" data-tilt="12">
                <div class="pop"><span class="stat-icon float-animal" style="animation-delay: -{{ $i }}s">{{ $icon }}</span></div>
                <div class="stat-value pop-sm" @if (! is_null($count)) data-count="{{ $count }}" @endif>{{ $text ?? $count }}</div>
                <div class="stat-label">{{ $label }}</div>
            </div>
        @endforeach
    </section>

    {{-- Menu cepat --}}
    <section class="mt-12">
        <div class="flex items-end justify-between mb-5">
            <div>
                <h2 class="section-title">Mau ngapain hari ini?</h2>
                <p class="section-sub">Pilih petualanganmu</p>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
            @foreach ($menus as $i => [$route, $icon, $title, $desc, $gradient])
                <a href="{{ route($route) }}" class="zoo-card p-5 sm:p-6 block reveal group" data-delay="{{ ($i % 3) + 1 }}" data-tilt="10">
                    <div class="pop w-16 h-16 rounded-2xl bg-gradient-to-br {{ $gradient }} flex items-center justify-center text-3xl shadow-lg transform group-hover:rotate-6 transition">{{ $icon }}</div>
                    <div class="pop-sm mt-4 font-display text-xl font-bold text-green-900">{{ $title }}</div>
                    <div class="text-sm font-semibold text-green-700">{{ $desc }}</div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Kartu profil --}}
    <section class="mt-12 grid md:grid-cols-3 gap-6">
        <div class="zoo-card leafy p-6 md:col-span-2 reveal">
            <h3 class="font-display text-2xl font-bold text-green-900 mb-4">🪪 Kartu Penjelajah</h3>
            <div class="grid sm:grid-cols-2 gap-4 text-sm">
                <div><div class="zoo-label">Username</div><div class="font-bold">{{ $user->username }}</div></div>
                <div><div class="zoo-label">Peran</div><span class="zoo-badge {{ $user->isAdmin() ? 'failed' : 'paid' }}">{{ ucfirst($user->role) }}</span></div>
                <div><div class="zoo-label">Email</div><div class="font-bold break-all">{{ $user->email }}</div></div>
                <div><div class="zoo-label">Telepon</div><div class="font-bold">{{ $user->phone ?? '-' }}</div></div>
            </div>
            <a href="{{ route('profil') }}" class="btn-zoo ghost mt-6">✏️ Ubah Profil</a>
        </div>
        <div class="zoo-card p-6 text-center reveal" data-delay="2" style="background: linear-gradient(160deg, #14532d, #052e16); color: #ecfdf5;">
            <div class="text-5xl float-animal">🌳</div>
            <h3 class="font-display text-xl font-bold mt-3">Tips Kunjungan</h3>
            <p class="text-sm mt-2 opacity-90">Datanglah pagi hari — banyak satwa sedang aktif mencari makan. Jangan lupa bawa botol minum isi ulang!</p>
        </div>
    </section>
</div>
@endsection
