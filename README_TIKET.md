# Sistem Pemesanan Tiket Safari - Dokumentasi

## Overview
Sistem pemesanan tiket yang lengkap dan profesional untuk Kebun Binatang Safari, dengan fitur dari pemesanan hingga pembayaran dan export tiket.

## Fitur Utama

### 1. Pemesanan Tiket (`tiket.php`)
- Formulir pemesanan dengan validasi
- Auto-fill data user yang login
- Penyimpanan pesanan ke database dengan status "pending"

### 2. Konfirmasi Pesanan (`tiket_konfirmasi.php`)
- Tampilkan detail pesanan sebelum pembayaran
- Validasi kepemilikan pesanan
- Tombol lanjut ke pembayaran

### 3. Pembayaran (`tiket_bayar.php`)
- Pilihan metode pembayaran (Transfer Bank, E-Wallet, Kartu Kredit)
- Simulasi pembayaran
- Update status pesanan menjadi "paid"
- **Notifikasi email otomatis** setelah pembayaran berhasil

### 4. Riwayat Pesanan (`tiket_riwayat.php`)
- Daftar semua pesanan user
- Status pembayaran dengan warna berbeda
- Tombol lanjutkan pembayaran untuk pesanan pending
- **Tombol export tiket** untuk pesanan yang sudah dibayar

### 5. Export Tiket (`tiket_export.php`)
- Tampilan tiket yang siap print
- QR Code placeholder
- Styling khusus untuk print
- Tombol print dan kembali

### 6. Dashboard Integration
- **Link "Riwayat Tiket"** di sidebar dashboard
- **Statistik tiket** di dashboard (Total Tiket, Tiket Lunas, Menunggu Bayar)
- Highlight menu aktif

## Database Schema

### Tabel `orders`
```sql
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    tanggal DATE NOT NULL,
    jumlah INT NOT NULL,
    kategori ENUM('dewasa', 'anak', 'keluarga') NOT NULL,
    status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    metode_pembayaran VARCHAR(50),
    waktu_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    waktu_bayar TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## Alur Sistem

1. **Pemesanan**: User mengisi form → Data disimpan dengan status "pending"
2. **Konfirmasi**: Tampilkan detail pesanan → User konfirmasi
3. **Pembayaran**: Pilih metode → Simulasi pembayaran → Status jadi "paid"
4. **Notifikasi**: Email otomatis dikirim (simulasi)
5. **Export**: User bisa print tiket yang sudah dibayar
6. **Riwayat**: Semua pesanan tersimpan dan bisa dilihat

## File yang Dibuat/Dimodifikasi

### File Baru:
- `tiket_konfirmasi.php` - Halaman konfirmasi pesanan
- `tiket_bayar.php` - Halaman pembayaran
- `tiket_riwayat.php` - Halaman riwayat pesanan
- `tiket_export.php` - Halaman export/print tiket
- `tiket-export.css` - CSS khusus untuk export tiket
- `README_TIKET.md` - Dokumentasi ini

### File Dimodifikasi:
- `tiket.php` - Tambah proses penyimpanan pesanan
- `dashboard.php` - Tambah link riwayat dan statistik tiket
- `setup_database.sql` - Tambah tabel orders
- `auth_system.sql` - Tambah tabel orders

## Fitur Tambahan

### 1. Notifikasi Email (Simulasi)
- Email dikirim otomatis setelah pembayaran berhasil
- Log email disimpan di `email_logs.txt`
- Template email HTML yang profesional

### 2. Statistik Dashboard
- Total tiket yang dipesan
- Jumlah tiket yang sudah dibayar
- Jumlah tiket yang menunggu pembayaran

### 3. Export Tiket
- Tampilan tiket yang profesional
- Optimized untuk print
- QR Code placeholder
- Informasi lengkap pesanan

### 4. Keamanan
- Validasi session di semua halaman
- Validasi kepemilikan pesanan
- Prepared statements untuk query database
- Escape HTML untuk mencegah XSS

## Cara Penggunaan

1. **Setup Database**: Jalankan `setup_database.sql` atau `auth_system.sql`
2. **Login**: Masuk ke sistem dengan akun yang sudah ada
3. **Pesan Tiket**: Klik "Pesan Tiket" di sidebar
4. **Isi Form**: Lengkapi formulir pemesanan
5. **Konfirmasi**: Cek detail pesanan
6. **Bayar**: Pilih metode pembayaran dan bayar
7. **Export**: Print tiket yang sudah dibayar
8. **Riwayat**: Lihat semua pesanan di "Riwayat Tiket"

## Catatan Teknis

- Sistem menggunakan PHP dengan MySQL
- Styling menggunakan Tailwind CSS
- Responsive design untuk mobile dan desktop
- Print-friendly untuk export tiket
- Simulasi pembayaran (tidak ada payment gateway asli)
- Simulasi email (log ke file, bukan kirim email asli)

## Pengembangan Selanjutnya

1. Integrasi payment gateway asli (Midtrans, Xendit, dll)
2. Email service asli (SMTP, SendGrid, dll)
3. QR Code generator untuk tiket
4. Sistem refund dan pembatalan
5. Notifikasi WhatsApp/SMS
6. Admin panel untuk kelola pesanan
7. Laporan penjualan tiket 