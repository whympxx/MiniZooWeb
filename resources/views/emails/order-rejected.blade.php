<html>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 28px;">❌ Pembayaran Tiket Ditolak</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px;">{{ config('app.name') }}</p>
    </div>
    <div style="background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;">
        <h2 style="color: #dc3545; margin-top: 0;">⚠️ Pembayaran Tidak Dapat Diproses</h2>
        <p>Halo <strong>{{ $order->nama }}</strong>,</p>
        <p>Mohon maaf, pembayaran untuk pesanan tiket Anda tidak dapat diproses.</p>
        <table style="width: 100%; border-collapse: collapse; background: white; border-left: 4px solid #dc3545;">
            <tr><td style="padding: 8px; font-weight: bold;">Order ID:</td><td style="padding: 8px;">#{{ $order->id }}</td></tr>
            <tr><td style="padding: 8px; font-weight: bold;">Kategori Tiket:</td><td style="padding: 8px;">{{ $order->kategoriLabel() }}</td></tr>
            <tr><td style="padding: 8px; font-weight: bold;">Jumlah Tiket:</td><td style="padding: 8px;">{{ $order->jumlah }} tiket</td></tr>
            <tr><td style="padding: 8px; font-weight: bold;">Tanggal Kunjungan:</td><td style="padding: 8px;">{{ $order->tanggal->translatedFormat('d F Y') }}</td></tr>
        </table>
        <p style="margin-top: 20px;">Silakan hubungi kami untuk informasi lebih lanjut atau lakukan pemesanan ulang dengan pembayaran yang valid.</p>
    </div>
    <p style="text-align: center; color: #666; font-size: 12px;">Email ini dikirim otomatis oleh sistem. Mohon tidak membalas email ini.</p>
</div>
</body>
</html>
