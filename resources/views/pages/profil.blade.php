@extends('layouts.zoo')

@section('title', 'Profil')

@section('content')
<div class="max-w-5xl mx-auto px-4 pt-10">
    <div class="grid md:grid-cols-5 gap-6 items-start">

        {{-- Kartu identitas 3D --}}
        <div class="md:col-span-2 reveal">
            <div class="ticket-3d p-6 text-center" data-tilt="14">
                <div class="stripe"></div>
                <div class="pop">
                    @if ($user->profile_picture)
                        <img src="{{ $user->profilePictureUrl() }}" alt="Foto Profil" class="w-28 h-28 mx-auto rounded-full object-cover border-4 border-yellow-300 shadow-xl">
                    @else
                        <div class="w-28 h-28 mx-auto rounded-full bg-yellow-300 text-green-900 flex items-center justify-center text-5xl font-bold border-4 border-white shadow-xl">{{ strtoupper(substr($user->username, 0, 1)) }}</div>
                    @endif
                </div>
                <div class="pop-sm mt-4">
                    <div class="text-xs uppercase tracking-widest text-green-200 font-bold">Kartu Penjelajah Safari</div>
                    <div class="font-display text-2xl font-bold mt-1">{{ $user->username }}</div>
                    <span class="zoo-badge mt-2 {{ $user->isAdmin() ? 'failed' : 'pending' }}">{{ $user->isAdmin() ? '🛠️ Admin' : '🧭 Penjelajah' }}</span>
                </div>
                <div class="perforation my-5"></div>
                <div class="grid grid-cols-2 gap-3 text-left text-sm">
                    <div><div class="text-green-200 text-xs font-bold">EMAIL</div><div class="font-bold break-all">{{ $user->email }}</div></div>
                    <div><div class="text-green-200 text-xs font-bold">TELEPON</div><div class="font-bold">{{ $user->phone ?? '-' }}</div></div>
                    <div><div class="text-green-200 text-xs font-bold">MEMBER SEJAK</div><div class="font-bold">{{ $user->created_at->translatedFormat('M Y') }}</div></div>
                    <div><div class="text-green-200 text-xs font-bold">UPDATE</div><div class="font-bold">{{ $user->updated_at->translatedFormat('d M Y') }}</div></div>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="zoo-card leafy p-6 sm:p-8 md:col-span-3 reveal" data-delay="2">
            <h1 class="font-display text-3xl font-bold text-green-900">✏️ Ubah Profil</h1>
            <p class="section-sub mb-5">Kosongkan kolom yang tidak ingin diubah.</p>

            @if (session('update_success'))
                <div class="zoo-alert success mb-4 pop-in">✅ {{ session('update_success') }}</div>
            @elseif ($errors->any())
                <div class="zoo-alert error mb-4">⚠️ {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="zoo-label" for="username">Username</label>
                    <div class="zoo-input-wrap">
                        <span class="icon">🧑</span>
                        <input id="username" type="text" name="username" value="{{ old('username', $user->username) }}" class="zoo-input">
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="zoo-label" for="old_password">Password Lama</label>
                        <div class="zoo-input-wrap">
                            <span class="icon">🔑</span>
                            <input id="old_password" type="password" name="old_password" class="zoo-input" autocomplete="current-password">
                        </div>
                    </div>
                    <div>
                        <label class="zoo-label" for="password">Password Baru</label>
                        <div class="zoo-input-wrap">
                            <span class="icon">🔒</span>
                            <input id="password" type="password" name="password" class="zoo-input" autocomplete="new-password">
                            <button type="button" class="toggle" data-toggle-password="password" aria-label="Tampilkan password">🙈</button>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="zoo-label" for="profile_picture">Foto Profil</label>
                    <label for="profile_picture" class="flex items-center gap-3 p-4 rounded-2xl border-2 border-dashed border-green-300 bg-green-50 cursor-pointer hover:bg-green-100 transition">
                        <span class="text-3xl">📸</span>
                        <span class="text-sm font-bold text-green-800" id="fileName">Pilih gambar (JPG, PNG, GIF, maks. 5 MB)</span>
                    </label>
                    <input id="profile_picture" type="file" name="profile_picture" accept="image/*" class="hidden"
                           onchange="document.getElementById('fileName').textContent = this.files[0] ? this.files[0].name : 'Pilih gambar'">
                </div>
                <button type="submit" class="btn-zoo w-full">💾 Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>
@endsection
