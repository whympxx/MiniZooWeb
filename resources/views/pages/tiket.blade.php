@extends('layouts.zoo')

@section('title', 'Pesan Tiket')

@section('content')
@php
    $categories = [
        'dewasa' => ['🧑', 'Dewasa', '17+ tahun'],
        'anak' => ['🧒', 'Anak-anak', '3–16 tahun'],
        'keluarga' => ['👨‍👩‍👧‍👦', 'Keluarga', '2 dewasa + 2 anak'],
    ];
@endphp

<div class="max-w-6xl mx-auto px-4 pt-10">
    <div class="text-center reveal">
        <span class="wood-sign">🎟️ Loket Online</span>
        <h1 class="section-title mt-4">Pesan Tiket Safari</h1>
        <p class="section-sub">Isi formulir, lalu lanjutkan ke pembayaran. Mudah dan cepat!</p>
    </div>

    <div class="grid lg:grid-cols-5 gap-8 mt-8 items-start">
        <form method="POST" action="{{ route('tiket.store') }}" class="zoo-card leafy p-6 sm:p-8 lg:col-span-3 space-y-5 reveal" autocomplete="off" id="ticketForm">
            @csrf
            @if ($errors->any())
                <div class="zoo-alert error">
                    <div>⚠️</div>
                    <div>@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
                </div>
            @endif

            <div>
                <span class="zoo-label">Kategori Tiket</span>
                <div class="grid grid-cols-3 gap-3">
                    @foreach ($categories as $key => [$emoji, $label, $desc])
                        <label class="choice-card">
                            <input type="radio" name="kategori" value="{{ $key }}" data-price="{{ $prices[$key] }}" data-label="{{ $label }}" data-emoji="{{ $emoji }}" required @checked(old('kategori', 'dewasa') === $key)>
                            <div class="choice-body">
                                <span class="emoji">{{ $emoji }}</span>
                                <div class="font-display font-bold text-green-900 mt-1">{{ $label }}</div>
                                <div class="text-xs font-semibold text-green-700">{{ $desc }}</div>
                                <div class="mt-2 font-extrabold text-orange-600 text-sm">Rp {{ number_format($prices[$key], 0, ',', '.') }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="zoo-label" for="nama">Nama Lengkap</label>
                    <div class="zoo-input-wrap">
                        <span class="icon">🧑</span>
                        <input id="nama" name="nama" type="text" required value="{{ old('nama', $user->username) }}" class="zoo-input" maxlength="100">
                    </div>
                </div>
                <div>
                    <label class="zoo-label" for="email">Email</label>
                    <div class="zoo-input-wrap">
                        <span class="icon">📧</span>
                        <input id="email" name="email" type="email" required value="{{ old('email', $user->email) }}" class="zoo-input">
                    </div>
                </div>
                <div>
                    <label class="zoo-label" for="tanggal">Tanggal Kunjungan</label>
                    <div class="zoo-input-wrap">
                        <span class="icon">📅</span>
                        <input id="tanggal" name="tanggal" type="date" required min="{{ now()->toDateString() }}" value="{{ old('tanggal', now()->addDay()->toDateString()) }}" class="zoo-input">
                    </div>
                </div>
                <div>
                    <label class="zoo-label" for="jumlah">Jumlah Tiket (maks. {{ $maxTickets }})</label>
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn-zoo ghost" style="padding: .7rem 1rem" data-step="-1" aria-label="Kurangi">➖</button>
                        <input id="jumlah" name="jumlah" type="number" min="1" max="{{ $maxTickets }}" required value="{{ old('jumlah', 1) }}" class="zoo-input text-center font-bold">
                        <button type="button" class="btn-zoo ghost" style="padding: .7rem 1rem" data-step="1" aria-label="Tambah">➕</button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between rounded-2xl p-4 bg-yellow-50 border-2 border-yellow-200">
                <span class="font-bold text-green-900">Total Pembayaran</span>
                <span class="font-display text-2xl font-bold text-orange-600" id="totalPrice">Rp 0</span>
            </div>

            <button type="submit" class="btn-zoo sunset w-full text-lg">🦁 Pesan Sekarang</button>
        </form>

        {{-- Pratinjau tiket 3D --}}
        <div class="lg:col-span-2 reveal" data-delay="2">
            <div class="ticket-3d p-6" data-tilt="16" style="min-height: 330px;">
                <div class="stripe"></div>
                <div class="flex items-center justify-between pop-sm">
                    <span class="font-display text-xl font-bold">🦁 Safari Zoo</span>
                    <span class="zoo-badge pending">PRATINJAU</span>
                </div>
                <div class="pop text-center my-6">
                    <div class="text-6xl float-animal" id="pvEmoji">🧑</div>
                    <div class="font-display text-2xl font-bold mt-2" id="pvLabel">Dewasa</div>
                </div>
                <div class="perforation mb-4"></div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><div class="text-green-200 text-xs font-bold">NAMA</div><div class="font-bold truncate" id="pvNama">-</div></div>
                    <div><div class="text-green-200 text-xs font-bold">TANGGAL</div><div class="font-bold" id="pvTanggal">-</div></div>
                    <div><div class="text-green-200 text-xs font-bold">JUMLAH</div><div class="font-bold" id="pvJumlah">1 tiket</div></div>
                    <div><div class="text-green-200 text-xs font-bold">TOTAL</div><div class="font-bold text-yellow-300" id="pvTotal">Rp 0</div></div>
                </div>
            </div>
            <p class="text-center text-sm font-semibold text-green-800 mt-4">🕗 Buka setiap hari {{ \App\Support\Settings::get('system_settings')['zoo_opening_hours'] }}–{{ \App\Support\Settings::get('system_settings')['zoo_closing_hours'] }} WIB</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var form = document.getElementById('ticketForm');
        var qty = document.getElementById('jumlah');
        var max = parseInt(qty.max, 10) || 10;
        var rupiah = function (n) { return 'Rp ' + n.toLocaleString('id-ID'); };

        function update() {
            var cat = form.querySelector('input[name="kategori"]:checked');
            var n = Math.min(Math.max(parseInt(qty.value, 10) || 0, 0), max);
            var total = cat ? parseInt(cat.dataset.price, 10) * n : 0;
            document.getElementById('totalPrice').textContent = rupiah(total);
            document.getElementById('pvTotal').textContent = rupiah(total);
            document.getElementById('pvJumlah').textContent = n + ' tiket';
            document.getElementById('pvNama').textContent = form.nama.value || '-';
            if (cat) {
                document.getElementById('pvEmoji').textContent = cat.dataset.emoji;
                document.getElementById('pvLabel').textContent = cat.dataset.label;
            }
            var d = form.tanggal.value ? new Date(form.tanggal.value + 'T00:00:00') : null;
            document.getElementById('pvTanggal').textContent = d ? d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
        }

        form.querySelectorAll('[data-step]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var v = (parseInt(qty.value, 10) || 0) + parseInt(btn.dataset.step, 10);
                qty.value = Math.min(Math.max(v, 1), max);
                update();
            });
        });
        form.addEventListener('input', update);
        form.addEventListener('change', update);
        update();
    })();
</script>
@endpush
