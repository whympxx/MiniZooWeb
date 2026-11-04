<html>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 28px;">🎫 Konfirmasi Pembayaran Tiket</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px;">{{ config('app.name') }}</p>
    </div>
    <div style="background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;">
        <h2 style="color: #28a745; margin-top: 0;">✅ Pembayaran Berhasil Dikonfirmasi</h2>
        <p>Halo <strong>{{ $order->nama }}</strong>,</p>
        <p>Pembayaran untuk pesanan tiket Anda telah berhasil dikonfirmasi. Berikut detail pesanan Anda:</p>
        <table style="width: 100%; border-collapse: collapse; background: white; padding: 20px; border-left: 4px solid #28a745;">
            <tr><td style="padding: 8px; font-weight: bold;">Order ID:</td><td style="padding: 8px;">#{{ $order->id }}</td></tr>
            <tr><td style="padding: 8px; font-weight: bold;">Kategori Tiket:</td><td style="padding: 8px;">{{ $order->kategoriLabel() }}</td></tr>
            <tr><td style="padding: 8px; font-weight: bold;">Jumlah Tiket:</td><td style="padding: 8px;">{{ $order->jumlah }} tiket</td></tr>
            <tr><td style="padding: 8px; font-weight: bold;">Tanggal Kunjungan:</td><td style="padding: 8px;">{{ $order->tanggal->translatedFormat('d F Y') }}</td></tr>
            <tr><td style="padding: 8px; font-weight: bold;">Metode Pembayaran:</td><td style="padding: 8px;">{{ $order->metode_pembayaran ?? '-' }}</td></tr>
            <tr><td style="padding: 8px; font-weight: bold;">Total Pembayaran:</td><td style="padding: 8px; color: #28a745; font-weight: bold;">Rp {{ number_format($order->totalPrice(), 0, ',', '.') }}</td></tr>
        </table>
        <p style="margin-top: 20px;">Silakan tunjukkan email ini atau e-tiket Anda saat check-in. Terima kasih telah memilih Kebun Binatang Safari!</p>
    </div>
    <p style="text-align: center; color: #666; font-size: 12px;">Email ini dikirim otomatis oleh sistem. Mohon tidak membalas email ini.</p>
</div>
</body>
</html>
