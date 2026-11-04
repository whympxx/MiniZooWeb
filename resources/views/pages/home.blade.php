@extends('layouts.zoo')

@section('title', 'Jelajah Satwa')

@section('content')
@php
    $animals = [
        ['Harimau.jpg', 'Harimau Sumatera', '🐯', 'Beristirahat di bawah rindangnya pepohonan.', 'Panthera tigris sumatrae', 'Hanya tersisa sekitar 400–600 ekor di alam liar. Belang tiap harimau unik seperti sidik jari.'],
        ['Gajah.jpg', 'Gajah Asia', '🐘', 'Menikmati mandi di kolam bersama kawanannya.', 'Elephas maximus', 'Bisa minum hingga 200 liter air per hari dan mengenali dirinya di cermin.'],
        ['Merak.jpg', 'Burung Merak', '🦚', 'Memamerkan bulu indahnya di pagi hari.', 'Pavo cristatus', 'Ekor merak jantan memiliki lebih dari 200 bulu dengan pola "mata".'],
        ['OrangUtan.jpg', 'Orangutan', '🦧', 'Bermain lincah di antara dahan pohon.', 'Pongo abelii', 'Membuat sarang baru setiap malam di atas pohon dan berbagi 97% DNA dengan manusia.'],
        ['jerapah.jpg', 'Jerapah', '🦒', 'Memakan daun dari pohon yang tinggi.', 'Giraffa camelopardalis', 'Lidahnya bisa mencapai 50 cm dan jantungnya seberat 11 kg.'],
        ['Panda.jpg', 'Panda', '🐼', 'Asyik makan bambu di habitatnya.', 'Ailuropoda melanoleuca', 'Menghabiskan 12–16 jam sehari untuk makan hingga 38 kg bambu.'],
    ];
@endphp

<div class="max-w-6xl mx-auto px-4 pt-10">
    {{-- Hero --}}
    <section class="text-center reveal">
        <span class="wood-sign">🌿 Selamat datang di</span>
        <h1 class="mt-5 font-display text-4xl sm:text-6xl font-bold text-green-900">Kebun Binatang <span class="text-orange-500">Safari</span></h1>
        <p class="mt-4 text-lg text-green-800 font-semibold max-w-2xl mx-auto">Temukan keajaiban dunia satwa, edukasi, dan petualangan seru bersama keluarga.</p>
        <div class="mt-6 text-5xl sm:text-6xl flex justify-center gap-4" aria-hidden="true">
            @foreach (['🦁', '🐘', '🦒', '🦓', '🐼'] as $i => $e)
                <span class="float-animal" style="animation-delay: -{{ $i * 0.8 }}s">{{ $e }}</span>
            @endforeach
        </div>
    </section>

    {{-- Zona --}}
    <section class="grid md:grid-cols-3 gap-6 mt-12">
        @foreach ([
            ['🦁', 'Zona Satwa', 'Koleksi satwa eksotis dari seluruh dunia, mulai dari mamalia, burung, hingga reptil langka.', 'from-yellow-200 to-orange-300'],
            ['🎉', 'Event & Edukasi', 'Pertunjukan, edukasi satwa, dan event seru yang diadakan setiap pekan.', 'from-blue-200 to-teal-300'],
            ['🍃', 'Wisata Alam', 'Taman bermain, area piknik, dan keindahan alam yang asri untuk keluarga.', 'from-green-200 to-green-400'],
        ] as $i => [$icon, $title, $desc, $g])
            <div class="zoo-card p-6 reveal" data-delay="{{ $i + 1 }}" data-tilt="12">
                <div class="pop w-16 h-16 rounded-2xl bg-gradient-to-br {{ $g }} flex items-center justify-center text-4xl shadow-lg">{{ $icon }}</div>
                <h3 class="pop-sm font-display text-2xl font-bold text-green-900 mt-4">{{ $title }}</h3>
                <p class="text-green-800 font-semibold mt-1">{{ $desc }}</p>
            </div>
        @endforeach
    </section>

    {{-- Galeri flip 3D --}}
    <section class="mt-16">
        <div class="text-center mb-8 reveal">
            <h2 class="section-title">Kenalan dengan Penghuni Safari</h2>
            <p class="section-sub">Arahkan kursor (atau ketuk) kartu untuk membalik dan melihat fakta uniknya 🔄</p>
        </div>
        <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach ($animals as $i => [$img, $name, $emoji, $caption, $latin, $fact])
                <div class="flip-card reveal" data-delay="{{ ($i % 3) + 1 }}" tabindex="0" onclick="this.classList.toggle('flipped')" onkeydown="if(event.key==='Enter')this.classList.toggle('flipped')">
                    <div class="flip-inner">
                        <div class="face front">
                            <img src="{{ asset('assets/images/'.$img) }}" alt="{{ $name }}" loading="lazy">
                            <div class="label">
                                <div class="font-display text-xl font-bold">{{ $emoji }} {{ $name }}</div>
                                <div class="text-sm opacity-90">{{ $caption }}</div>
                            </div>
                        </div>
                        <div class="face back">
                            <div class="text-5xl">{{ $emoji }}</div>
                            <div class="font-display text-2xl font-bold mt-2">{{ $name }}</div>
                            <div class="text-sm italic text-green-200">{{ $latin }}</div>
                            <p class="mt-3 font-semibold">💡 {{ $fact }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Kenapa Safari --}}
    <section class="zoo-card leafy mt-16 p-6 sm:p-10 grid md:grid-cols-2 gap-8 items-center reveal">
        <div class="relative" data-tilt="8" style="border-radius: 24px;">
            <img src="{{ asset('assets/images/register.jpg') }}" alt="Keluarga di Safari Zoo" class="w-full h-64 object-cover rounded-3xl shadow-xl" loading="lazy">
            <span class="pop absolute -bottom-4 -right-2 text-6xl float-animal">🦓</span>
        </div>
        <div>
            <h2 class="section-title">Kenapa Memilih Safari Zoo?</h2>
            <ul class="mt-4 space-y-3 font-semibold text-green-900">
                @foreach (['🐾 Lebih dari 200 spesies satwa dari seluruh dunia', '🎓 Fasilitas edukasi dan event interaktif', '🛝 Area bermain dan piknik keluarga', '🌳 Lingkungan asri dan ramah anak', '🛡️ Wisata yang aman dan menyenangkan'] as $point)
                    <li class="flex gap-2">{{ $point }}</li>
                @endforeach
            </ul>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('peta') }}" class="btn-zoo sky">🗺️ Lihat Peta</a>
                <a href="{{ route('tiket.create') }}" class="btn-zoo">🎟️ Pesan Tiket</a>
            </div>
        </div>
    </section>
</div>
@endsection
