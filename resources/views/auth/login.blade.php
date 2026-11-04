@extends('layouts.zoo')

@section('title', 'Masuk')
@section('no-nav', true)
@include('partials.three')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-5xl grid lg:grid-cols-2 gap-8 items-center">

        {{-- Panel 3D --}}
        <div class="hero-3d hidden lg:block reveal" data-zoo-scene style="min-height: 560px;">
            <div class="hero-content p-8">
                <span class="wood-sign">🌿 Selamat Datang</span>
                <h1 class="mt-5 text-4xl font-bold text-green-900 leading-tight">Petualangan<br>Safari Menantimu!</h1>
                <p class="mt-2 text-green-800 font-semibold max-w-xs">Masuk untuk memesan tiket dan menjelajahi dunia satwa.</p>
            </div>
        </div>

        {{-- Form --}}
        <div class="zoo-card leafy p-8 sm:p-10 pop-in" data-tilt="4">
            <div class="text-center mb-6 pop-sm">
                <div class="text-6xl float-animal">🦁</div>
                <h2 class="text-3xl font-bold text-green-900 mt-2">Masuk ke Safari Zoo</h2>
                <p class="section-sub">Senang melihatmu kembali, penjelajah!</p>
            </div>

            @if ($errors->any())
                <div class="zoo-alert error mb-4">⚠️ {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="zoo-label" for="email">Email</label>
                    <div class="zoo-input-wrap">
                        <span class="icon">📧</span>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" class="zoo-input" placeholder="nama@email.com" required autofocus autocomplete="email">
                    </div>
                </div>
                <div>
                    <label class="zoo-label" for="password">Password</label>
                    <div class="zoo-input-wrap">
                        <span class="icon">🔒</span>
                        <input id="password" name="password" type="password" class="zoo-input" placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="toggle" data-toggle-password="password" aria-label="Tampilkan password">🙈</button>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm font-bold text-green-800">
                    <input type="checkbox" name="remember" class="w-4 h-4"> Ingat saya
                </label>
                <button type="submit" class="btn-zoo w-full text-lg">🐾 Masuk</button>
            </form>

            <p class="text-center mt-6 text-sm font-semibold text-green-800">
                Belum punya akun?
                <a href="{{ route('register') }}" class="text-orange-600 hover:underline font-bold">Daftar sekarang</a>
            </p>
        </div>
    </div>
</div>
@endsection
