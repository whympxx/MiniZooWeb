@extends('layouts.zoo')

@section('title', 'E-Tiket #'.$order->id)
@section('no-nav', true)

@push('head')
<style>
    @media print {
        .no-print, .zoo-scenery { display: none !important; }
        body.zoo-body { background: #fff !important; }
        .ticket-3d { box-shadow: none !important; transform: none !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
    .barcode { height: 54px; background: repeating-linear-gradient(90deg, #052e16 0 2px, transparent 2px 4px, #052e16 4px 7px, transparent 7px 9px, #052e16 9px 10px, transparent 10px 13px); border-radius: 4px; }
</style>
@endpush

@section('content')
@php $emoji = ['dewasa' => '🧑', 'anak' => '🧒', 'keluarga' => '👨‍👩‍👧‍👦'][$order->kategori] ?? '🎟️'; @endphp
<div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-lg">
        <div class="ticket-3d pop-in" data-tilt="10">
            <div class="stripe"></div>
            <div class="p-7 text-center">
                <div class="pop text-5xl">🦁</div>
                <h1 class="font-display text-2xl font-bold mt-2">E-Tiket Kebun Binatang Safari</h1>
                <p class="text-green-200 text-sm font-semibold">Tunjukkan tiket ini saat check-in</p>
            </div>
            <div class="perforation mx-6"></div>
            <div class="p-7 space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-green-200">Kode Tiket</span><span class="font-mono font-bold text-yellow-300 text-base">ZOO-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span></div>
                <div class="flex justify-between"><span class="text-green-200">Nama</span><span class="font-bold">{{ $order->nama }}</span></div>
                <div class="flex justify-between"><span class="text-green-200">Tanggal Kunjungan</span><span class="font-bold">{{ $order->tanggal->translatedFormat('d F Y') }}</span></div>
                <div class="flex justify-between"><span class="text-green-200">Kategori</span><span class="font-bold">{{ $emoji }} {{ $order->kategoriLabel() }}</span></div>
                <div class="flex justify-between"><span class="text-green-200">Jumlah</span><span class="font-bold">{{ $order->jumlah }} tiket</span></div>
                <div class="flex justify-between"><span class="text-green-200">Metode Pembayaran</span><span class="font-bold">{{ $order->metode_pembayaran ?? '-' }}</span></div>
                <div class="flex justify-between items-center pt-2">
                    <span class="text-green-200">Total Dibayar</span>
                    <span class="font-display text-2xl font-bold text-yellow-300">Rp {{ number_format($order->totalPrice(), 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="perforation mx-6"></div>
            <div class="p-7">
                <div class="bg-white rounded-xl p-3"><div class="barcode"></div></div>
                <p class="text-xs text-green-200 text-center mt-3">Dibayar pada {{ $order->waktu_bayar?->format('d/m/Y H:i') ?? '-' }}</p>
            </div>
        </div>

        <div class="no-print flex gap-3 mt-8">
            <button onclick="window.print()" class="btn-zoo sky flex-1">🖨️ Cetak / Simpan PDF</button>
            <a href="{{ route('tiket.riwayat') }}" class="btn-zoo flex-1">📋 Riwayat Pesanan</a>
        </div>
    </div>
</div>
@endsection
