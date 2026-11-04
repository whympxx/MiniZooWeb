@extends('layouts.zoo')

@section('title', 'Konfirmasi Pesanan')

@section('content')
@php $emoji = ['dewasa' => '🧑', 'anak' => '🧒', 'keluarga' => '👨‍👩‍👧‍👦'][$order->kategori] ?? '🎟️'; @endphp
<div class="max-w-3xl mx-auto px-4 pt-10">
    <div class="text-center reveal">
        <div class="text-6xl float-animal">🦜</div>
        <h1 class="section-title mt-2">Pesanan Berhasil Dibuat!</h1>
        <p class="section-sub">Periksa kembali detail pesananmu sebelum membayar.</p>
    </div>

    <div class="ticket-3d mt-8 pop-in" data-tilt="8">
        <div class="stripe"></div>
        <div class="p-6 sm:p-8 flex items-center justify-between gap-4">
            <div class="pop-sm">
                <div class="text-xs uppercase tracking-widest text-green-200 font-bold">Pesanan</div>
                <div class="font-display text-3xl font-bold">#{{ $order->id }}</div>
            </div>
            <div class="pop text-6xl">{{ $emoji }}</div>
        </div>
        <div class="perforation mx-6"></div>
        <div class="p-6 sm:p-8 grid grid-cols-2 sm:grid-cols-3 gap-5 text-sm">
            <div><div class="text-green-200 text-xs font-bold">NAMA</div><div class="font-bold text-base">{{ $order->nama }}</div></div>
            <div><div class="text-green-200 text-xs font-bold">EMAIL</div><div class="font-bold text-base break-all">{{ $order->email }}</div></div>
            <div><div class="text-green-200 text-xs font-bold">TANGGAL KUNJUNGAN</div><div class="font-bold text-base">{{ $order->tanggal->translatedFormat('d F Y') }}</div></div>
            <div><div class="text-green-200 text-xs font-bold">KATEGORI</div><div class="font-bold text-base">{{ $order->kategoriLabel() }}</div></div>
            <div><div class="text-green-200 text-xs font-bold">JUMLAH</div><div class="font-bold text-base">{{ $order->jumlah }} tiket</div></div>
            <div><div class="text-green-200 text-xs font-bold">STATUS</div><span class="zoo-badge {{ $order->status }}">{{ $order->statusLabel() }}</span></div>
        </div>
        <div class="px-6 sm:px-8 pb-6 flex items-center justify-between">
            <span class="text-green-200 font-bold">Total</span>
            <span class="font-display text-3xl font-bold text-yellow-300">Rp {{ number_format($order->totalPrice(), 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 mt-8 reveal">
        @if ($order->status === 'pending')
            <a href="{{ route('tiket.bayar', $order) }}" class="btn-zoo sunset flex-1 text-lg">💳 Lanjut ke Pembayaran</a>
        @endif
        <a href="{{ route('tiket.create') }}" class="btn-zoo ghost flex-1">🎟️ Pesan Lagi</a>
        <a href="{{ route('tiket.riwayat') }}" class="btn-zoo ghost flex-1">📋 Riwayat</a>
    </div>
</div>
@endsection
