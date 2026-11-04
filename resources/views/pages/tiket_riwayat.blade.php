@extends('layouts.zoo')

@section('title', 'Riwayat Tiket')

@section('content')
@php $emojis = ['dewasa' => '🧑', 'anak' => '🧒', 'keluarga' => '👨‍👩‍👧‍👦']; @endphp
<div class="max-w-5xl mx-auto px-4 pt-10">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 reveal">
        <div>
            <span class="wood-sign">📋 Koleksi Tiketmu</span>
            <h1 class="section-title mt-4">Riwayat Pesanan</h1>
            <p class="section-sub">{{ $orders->count() }} pesanan · {{ $orders->where('status', 'paid')->count() }} lunas</p>
        </div>
        <a href="{{ route('tiket.create') }}" class="btn-zoo">🎟️ Pesan Tiket Baru</a>
    </div>

    @if ($orders->isEmpty())
        <div class="zoo-card leafy p-10 mt-8 text-center reveal">
            <div class="text-7xl float-animal">🦥</div>
            <h2 class="font-display text-2xl font-bold text-green-900 mt-4">Belum ada pesanan</h2>
            <p class="section-sub">Si sloth masih menunggu kunjungan pertamamu!</p>
            <a href="{{ route('tiket.create') }}" class="btn-zoo sunset mt-6">🦁 Pesan Tiket Pertama</a>
        </div>
    @else
        <div class="space-y-5 mt-8">
            @foreach ($orders as $i => $order)
                <article class="zoo-card overflow-hidden reveal" data-delay="{{ min($i + 1, 5) }}" data-tilt="4">
                    <div class="flex flex-col sm:flex-row">
                        <div class="sm:w-40 p-5 flex sm:flex-col items-center justify-center gap-3 text-white"
                             style="background: linear-gradient(160deg, {{ $order->status === 'paid' ? '#16a34a, #0b3d22' : ($order->status === 'pending' ? '#f59e0b, #b45309' : '#ef4444, #7f1d1d') }});">
                            <span class="pop text-5xl">{{ $emojis[$order->kategori] ?? '🎟️' }}</span>
                            <span class="pop-sm font-display text-2xl font-bold">#{{ $order->id }}</span>
                        </div>
                        <div class="flex-1 p-5 sm:p-6">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="font-display text-xl font-bold text-green-900">{{ $order->kategoriLabel() }} · {{ $order->jumlah }} tiket</div>
                                <span class="zoo-badge {{ $order->status }}">{{ $order->statusLabel() }}</span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4 text-sm">
                                <div><div class="zoo-label">Pemesan</div><div class="font-bold">{{ $order->nama }}</div></div>
                                <div><div class="zoo-label">Kunjungan</div><div class="font-bold">{{ $order->tanggal->translatedFormat('d M Y') }}</div></div>
                                <div><div class="zoo-label">Dipesan</div><div class="font-bold">{{ $order->waktu_pesan?->format('d/m/Y H:i') ?? '-' }}</div></div>
                                <div><div class="zoo-label">Total</div><div class="font-bold text-orange-600">Rp {{ number_format($order->totalPrice(), 0, ',', '.') }}</div></div>
                            </div>
                            @if ($order->status === 'paid')
                                <p class="text-xs font-semibold text-green-700 mt-3">💳 {{ $order->metode_pembayaran ?? '-' }} · dibayar {{ $order->waktu_bayar?->format('d/m/Y H:i') ?? '-' }}</p>
                            @endif
                            <div class="mt-4 flex flex-wrap gap-3">
                                @if ($order->status === 'pending')
                                    <a href="{{ route('tiket.bayar', $order) }}" class="btn-zoo sunset">💳 Lanjutkan Pembayaran</a>
                                @elseif ($order->status === 'paid')
                                    <a href="{{ route('tiket.eticket', $order) }}" class="btn-zoo sky">📄 Lihat E-Tiket</a>
                                @endif
                                <a href="{{ route('tiket.konfirmasi', $order) }}" class="btn-zoo ghost">🔍 Detail</a>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
