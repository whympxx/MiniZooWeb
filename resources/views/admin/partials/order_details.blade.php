@php
    $statusColors = ['pending' => 'bg-yellow-100 text-yellow-800', 'paid' => 'bg-green-100 text-green-800', 'failed' => 'bg-red-100 text-red-800'];
    $categoryInfo = [
        'dewasa' => 'Tiket untuk pengunjung dewasa (17+ tahun)',
        'anak' => 'Tiket untuk pengunjung anak-anak (3-16 tahun)',
        'keluarga' => 'Paket tiket keluarga (2 dewasa + 2 anak)',
    ];
@endphp
<div class="space-y-4 text-sm">
    <div class="flex items-center justify-between">
        <h4 class="text-lg font-semibold text-gray-800">Pesanan #{{ $order->id }}</h4>
        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
            {{ $order->statusLabel() }}
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-gray-50 rounded-lg p-4">
            <h5 class="font-semibold text-gray-700 mb-2"><i class="fas fa-user mr-2"></i>Pemesan</h5>
            <p><span class="text-gray-500">Nama:</span> {{ $order->nama }}</p>
            <p><span class="text-gray-500">Email:</span> {{ $order->email }}</p>
            <p><span class="text-gray-500">Akun:</span> {{ $order->user?->username ?? '-' }}</p>
            <p><span class="text-gray-500">Telepon akun:</span> {{ $order->user?->phone ?? '-' }}</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-4">
            <h5 class="font-semibold text-gray-700 mb-2"><i class="fas fa-ticket-alt mr-2"></i>Tiket</h5>
            <p><span class="text-gray-500">Kategori:</span> {{ $order->kategoriLabel() }}</p>
            <p class="text-xs text-gray-500">{{ $categoryInfo[$order->kategori] ?? '' }}</p>
            <p><span class="text-gray-500">Jumlah:</span> {{ $order->jumlah }} tiket</p>
            <p><span class="text-gray-500">Tanggal kunjungan:</span> {{ $order->tanggal->translatedFormat('d F Y') }}</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-4">
            <h5 class="font-semibold text-gray-700 mb-2"><i class="fas fa-money-bill mr-2"></i>Pembayaran</h5>
            <p><span class="text-gray-500">Harga per tiket:</span> Rp {{ number_format($order->pricePerTicket(), 0, ',', '.') }}</p>
            <p><span class="text-gray-500">Total:</span> <strong>Rp {{ number_format($order->totalPrice(), 0, ',', '.') }}</strong></p>
            <p><span class="text-gray-500">Metode:</span> {{ $order->metode_pembayaran ?? '-' }}</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-4">
            <h5 class="font-semibold text-gray-700 mb-2"><i class="fas fa-clock mr-2"></i>Waktu</h5>
            <p><span class="text-gray-500">Dipesan:</span> {{ $order->waktu_pesan?->format('d M Y H:i') ?? '-' }}</p>
            <p><span class="text-gray-500">Dibayar:</span> {{ $order->waktu_bayar?->format('d M Y H:i') ?? '-' }}</p>
        </div>
    </div>
</div>
