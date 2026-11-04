@extends('layouts.zoo')

@section('title', 'Daftar')
@section('no-nav', true)
@include('partials.three')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-5xl grid lg:grid-cols-2 gap-8 items-center">

        <div class="zoo-card leafy p-8 sm:p-10 pop-in order-2 lg:order-1" data-tilt="3">
            <div class="text-center mb-6">
                <div class="text-6xl float-animal">🦒</div>
                <h2 class="text-3xl font-bold text-green-900 mt-2">Buat Akun Baru</h2>
                <p class="section-sub">Gabung dan mulai petualangan safarimu.</p>
            </div>

            @if ($errors->any())
                <div class="zoo-alert error mb-4">
                    <div>⚠️</div>
                    <div>@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="zoo-label" for="name">Username</label>
                    <div class="zoo-input-wrap">
                        <span class="icon">🧑</span>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" class="zoo-input" placeholder="Minimal 3 karakter" minlength="3" maxlength="50" required>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="zoo-label" for="email">Email</label>
                        <div class="zoo-input-wrap">
                            <span class="icon">📧</span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" class="zoo-input" placeholder="nama@email.com" required>
                        </div>
                    </div>
                    <div>
                        <label class="zoo-label" for="phone">No. Telepon</label>
                        <div class="zoo-input-wrap">
                            <span class="icon">📱</span>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="zoo-input" placeholder="081234567890" pattern="\d{10,20}" required>
                        </div>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="zoo-label" for="password">Password</label>
                        <div class="zoo-input-wrap">
                            <span class="icon">🔒</span>
                            <input id="password" name="password" type="password" class="zoo-input" placeholder="••••••••" minlength="6" required>
                            <button type="button" class="toggle" data-toggle-password="password" aria-label="Tampilkan password">🙈</button>
                        </div>
                    </div>
                    <div>
                        <label class="zoo-label" for="password_confirmation">Ulangi Password</label>
                        <div class="zoo-input-wrap">
                            <span class="icon">🔁</span>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="zoo-input" placeholder="••••••••" required>
                        </div>
                    </div>
                </div>
                <p class="text-xs font-semibold text-green-700">Password minimal 6 karakter, berisi huruf besar, huruf kecil, dan angka.</p>
                <label class="flex items-center gap-2 text-sm font-bold text-green-800">
                    <input type="checkbox" name="terms" class="w-4 h-4" required @checked(old('terms'))> Saya setuju dengan syarat & ketentuan
                </label>
                <button type="submit" class="btn-zoo sunset w-full text-lg">🌱 Daftar</button>
            </form>

            <p class="text-center mt-6 text-sm font-semibold text-green-800">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-green-700 hover:underline font-bold">Masuk di sini</a>
            </p>
        </div>

        <div class="hero-3d hidden lg:block reveal order-1 lg:order-2" data-zoo-scene style="min-height: 600px;">
            <div class="hero-content p-8 text-right">
                <span class="wood-sign">🎟️ Daftar Gratis</span>
                <h1 class="mt-5 text-4xl font-bold text-green-900 leading-tight">200+ Satwa<br>Siap Menyapa!</h1>
                <p class="mt-2 text-green-800 font-semibold ml-auto max-w-xs">Pesan tiket online, simpan e-tiket, dan pantau kunjunganmu.</p>
            </div>
        </div>
    </div>
</div>
@endsection
