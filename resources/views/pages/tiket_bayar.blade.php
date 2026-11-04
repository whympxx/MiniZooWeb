@extends('layouts.zoo')

@section('title', 'Pembayaran')

@push('head')
<style>
    .confetti { position: fixed; top: -20px; z-index: 60; pointer-events: none; font-size: 22px; animation: confetti-fall linear forwards; }
    @keyframes confetti-fall {
        to { transform: translate3d(var(--dx), 110vh, 0) rotateX(720deg) rotateY(540deg); }
    }
    .success-badge { width: 120px; height: 120px; border-radius: 50%; margin: 0 auto; display: grid; place-items: center; font-size: 56px;
        background: radial-gradient(circle at 35% 30%, #86efac, #16a34a); box-shadow: 0 20px 40px -12px rgba(22,101,52,.6), inset 0 -8px 0 rgba(0,0,0,.15);
        animation: badge-spin 1.4s cubic-bezier(.2,.8,.2,1) both; }
    @keyframes badge-spin { from { transform: perspective(600px) rotateY(-540deg) scale(.3); opacity: 0; } to { transform: none; opacity: 1; } }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto px-4 pt-10">
    @if ($success)
        <div class="zoo-card leafy max-w-xl mx-auto p-8 sm:p-10 text-center pop-in">
            <div class="success-badge">✅</div>
            <h1 class="section-title mt-6">Pembayaran Berhasil!</h1>
            <p class="section-sub mt-1">Tiket pesanan #{{ $order_id }} sudah dikonfirmasi dan email notifikasi telah dikirim.</p>
            <div class="flex flex-col sm:flex-row gap-3 mt-8">
                <a href="{{ route('tiket.eticket', $order) }}" class="btn-zoo sky flex-1">📄 Lihat E-Tiket</a>
                <a href="{{ route('tiket.riwayat') }}" class="btn-zoo flex-1">📋 Riwayat Pesanan</a>
            </div>
        </div>
    @else
        <div class="text-center reveal">
            <span class="wood-sign">💳 Pembayaran</span>
            <h1 class="section-title mt-4">Selesaikan Pembayaran</h1>
        </div>

        <div class="grid lg:grid-cols-5 gap-8 mt-8 items-start">
            {{-- Ringkasan --}}
            <div class="lg:col-span-2 reveal">
                <div class="ticket-3d p-6" data-tilt="12">
                    <div class="stripe"></div>
                    <div class="pop-sm text-xs uppercase tracking-widest text-green-200 font-bold">Ringkasan Pesanan</div>
                    <div class="pop font-display text-3xl font-bold mt-1">#{{ $order_id }}</div>
                    <div class="perforation my-4"></div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-green-200">Nama</dt><dd class="font-bold">{{ $order->nama }}</dd></div>
                        <div class="flex justify-between"><dt class="text-green-200">Tanggal</dt><dd class="font-bold">{{ $order->tanggal->format('d/m/Y') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-green-200">Kategori</dt><dd class="font-bold">{{ $order->kategoriLabel() }}</dd></div>
                        <div class="flex justify-between"><dt class="text-green-200">Jumlah</dt><dd class="font-bold">{{ $order->jumlah }} × Rp {{ number_format($order->pricePerTicket(), 0, ',', '.') }}</dd></div>
                    </dl>
                    <div class="perforation my-4"></div>
                    <div class="flex justify-between items-center">
                        <span class="font-bold">Total</span>
                        <span class="font-display text-2xl font-bold text-yellow-300">Rp {{ number_format($order->totalPrice(), 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Metode --}}
            <div class="zoo-card leafy p-6 sm:p-8 lg:col-span-3 reveal" data-delay="2">
                @if ($errors->any())
                    <div class="zoo-alert error mb-5">⚠️ {{ $errors->first() }}</div>
                @endif

                @if ($order->status !== 'pending')
                    <div class="text-center py-6">
                        <div class="text-5xl">🦉</div>
                        <p class="font-bold text-green-900 mt-3">Pesanan ini sudah berstatus <span class="zoo-badge {{ $order->status }}">{{ $order->statusLabel() }}</span></p>
                        <a href="{{ route('tiket.riwayat') }}" class="btn-zoo mt-6">📋 Kembali ke Riwayat</a>
                    </div>
                @else
                    <form method="POST" action="{{ route('tiket.bayar.store', $order) }}" class="space-y-5" id="payForm">
                        @csrf
                        <span class="zoo-label">Pilih Metode Pembayaran</span>
                        <div class="grid sm:grid-cols-3 gap-3">
                            @foreach ([['Transfer Bank', '🏦', 'BCA, BNI, Mandiri'], ['E-Wallet', '📱', 'GoPay, OVO, DANA'], ['Kartu Kredit', '💳', 'Visa, Mastercard']] as [$value, $icon, $desc])
                                <label class="choice-card">
                                    <input type="radio" name="metode" value="{{ $value }}" required @checked(old('metode') === $value)>
                                    <div class="choice-body">
                                        <span class="emoji">{{ $icon }}</span>
                                        <div class="font-display font-bold text-green-900 mt-1">{{ $value }}</div>
                                        <div class="text-xs font-semibold text-green-700">{{ $desc }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs font-semibold text-green-700">🔒 Ini adalah simulasi pembayaran — tidak ada dana yang ditarik.</p>
                        <button type="submit" class="btn-zoo sunset w-full text-lg" id="payBtn">💰 Bayar Rp {{ number_format($order->totalPrice(), 0, ',', '.') }}</button>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    @if ($success)
        (function () {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            var items = ['🎉', '🎊', '🦁', '🐘', '🦒', '🍃', '⭐', '🐼'];
            for (var i = 0; i < 40; i++) {
                var s = document.createElement('span');
                s.className = 'confetti';
                s.textContent = items[i % items.length];
                s.style.left = Math.random() * 100 + 'vw';
                s.style.setProperty('--dx', (Math.random() * 200 - 100) + 'px');
                s.style.animationDuration = (2.5 + Math.random() * 2.5) + 's';
                s.style.animationDelay = (Math.random() * 0.8) + 's';
                document.body.appendChild(s);
                setTimeout(function (el) { el.remove(); }, 6000, s);
            }
        })();
    @endif
    (function () {
        var form = document.getElementById('payForm');
        if (!form) return;
        form.addEventListener('submit', function () {
            var btn = document.getElementById('payBtn');
            btn.disabled = true;
            btn.textContent = '⏳ Memproses...';
        });
    })();
</script>
@endpush
