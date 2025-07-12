# Manajemen Tiket Admin - Zoo Management System

## Deskripsi
Fitur manajemen tiket memungkinkan admin untuk mengelola dan mengkonfirmasi pesanan tiket dari pengguna. Admin dapat melihat semua pesanan, mengkonfirmasi pembayaran, menolak pesanan, dan melakukan bulk actions.

## Fitur Utama

### 1. Dashboard Statistik
- **Total Pesanan**: Menampilkan jumlah total pesanan tiket
- **Menunggu Konfirmasi**: Pesanan dengan status pending
- **Dikonfirmasi**: Pesanan yang sudah dikonfirmasi pembayarannya
- **Ditolak**: Pesanan yang ditolak admin
- **Total Pendapatan**: Total pendapatan dari pesanan yang dikonfirmasi

### 2. Manajemen Pesanan
- **Tabel Pesanan**: Menampilkan semua pesanan dengan informasi lengkap
- **Filter dan Pencarian**: 
  - Pencarian berdasarkan nama pemesan
  - Filter berdasarkan status (pending/paid/failed)
  - Filter berdasarkan kategori tiket (dewasa/anak/keluarga)

### 3. Aksi Individual
- **Konfirmasi Pesanan**: Mengubah status dari pending ke paid
- **Tolak Pesanan**: Mengubah status dari pending ke failed
- **Lihat Detail**: Melihat informasi lengkap pesanan
- **Hapus Pesanan**: Menghapus pesanan dari database

### 4. Bulk Actions
- **Pilih Semua**: Memilih semua pesanan sekaligus
- **Bulk Konfirmasi**: Mengkonfirmasi beberapa pesanan sekaligus
- **Bulk Tolak**: Menolak beberapa pesanan sekaligus
- **Bulk Hapus**: Menghapus beberapa pesanan sekaligus
- **Bulk Export**: Export data pesanan ke CSV

### 5. Detail Pesanan
- **Informasi Pemesan**: Nama, email, username, phone
- **Detail Tiket**: Kategori, jumlah, tanggal kunjungan, harga
- **Informasi Pembayaran**: Total harga, status, metode pembayaran
- **Timeline**: Waktu pesan dan waktu konfirmasi

## File yang Dibuat

### 1. `admin_tiket_management.php`
Halaman utama manajemen tiket dengan fitur:
- Dashboard statistik
- Tabel pesanan dengan filter dan pencarian
- Bulk actions
- Modal konfirmasi dan detail

### 2. `get_order_details.php`
API untuk mengambil detail pesanan via AJAX:
- Mengambil data pesanan berdasarkan ID
- Menghitung harga berdasarkan kategori
- Generate HTML untuk modal detail

### 3. `admin_tiket_bulk_actions.php`
API untuk menangani bulk actions:
- Bulk konfirmasi pesanan
- Bulk tolak pesanan
- Bulk hapus pesanan
- Export data ke CSV

### 4. `admin-tiket-management.css`
Styling khusus untuk halaman manajemen tiket:
- Animasi dan transisi
- Status badges dengan gradient
- Responsive design
- Custom scrollbar

## Struktur Database

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

## Harga Tiket
- **Dewasa**: Rp 50.000 per tiket
- **Anak-anak**: Rp 30.000 per tiket
- **Keluarga**: Rp 120.000 per paket

## Cara Penggunaan

### 1. Akses Halaman
1. Login sebagai admin
2. Klik menu "Manajemen Tiket" di navigation bar
3. Atau klik card "Manajemen Tiket" di dashboard admin

### 2. Melihat Pesanan
1. Semua pesanan ditampilkan dalam tabel
2. Gunakan filter untuk melihat pesanan berdasarkan status atau kategori
3. Gunakan search box untuk mencari pesanan berdasarkan nama

### 3. Konfirmasi Pesanan
1. Klik icon check (✓) pada baris pesanan yang ingin dikonfirmasi
2. Atau pilih beberapa pesanan dengan checkbox
3. Klik tombol "Konfirmasi" untuk bulk action
4. Konfirmasi aksi di modal yang muncul

### 4. Lihat Detail Pesanan
1. Klik icon eye (👁️) pada baris pesanan
2. Modal akan menampilkan informasi lengkap pesanan
3. Dari modal detail, admin juga bisa konfirmasi atau tolak pesanan

### 5. Export Data
1. Pilih pesanan yang ingin di-export dengan checkbox
2. Klik tombol "Export"
3. File CSV akan otomatis terdownload

## Keamanan

### 1. Autentikasi
- Hanya admin yang bisa mengakses halaman manajemen tiket
- Session check pada setiap request

### 2. Validasi Input
- Validasi order ID sebelum melakukan aksi
- Sanitasi input untuk mencegah SQL injection
- CSRF protection dengan session validation

### 3. Error Handling
- Try-catch untuk database operations
- Proper HTTP status codes
- User-friendly error messages

## Responsive Design

### Desktop
- Tabel dengan semua kolom ditampilkan
- Side-by-side layout untuk filter dan search
- Hover effects pada cards dan buttons

### Tablet
- Tabel dengan scroll horizontal
- Stacked layout untuk filter dan search
- Adjusted button sizes

### Mobile
- Responsive table dengan minimal columns
- Full-width layout
- Touch-friendly buttons

## Integrasi

### 1. Admin Dashboard
- Link ke manajemen tiket ditambahkan di navigation
- Card manajemen tiket ditambahkan di quick actions
- Statistik tiket ditampilkan di dashboard utama

### 2. Database
- Menggunakan tabel `orders` yang sudah ada
- Foreign key ke tabel `users` untuk data pemesan
- Timestamp untuk tracking waktu

### 3. Styling
- Menggunakan Tailwind CSS untuk styling utama
- Custom CSS untuk animasi dan efek khusus
- Font Awesome untuk icons

## Maintenance

### 1. Backup Data
- Regular backup tabel `orders`
- Export data penting secara berkala

### 2. Monitoring
- Monitor jumlah pesanan pending
- Track revenue dari pesanan yang dikonfirmasi
- Log aksi admin untuk audit trail

### 3. Updates
- Update harga tiket jika diperlukan
- Tambah kategori tiket baru jika diperlukan
- Improve UI/UX berdasarkan feedback

## Troubleshooting

### 1. Pesanan Tidak Muncul
- Cek koneksi database
- Pastikan tabel `orders` ada dan berisi data
- Cek foreign key constraint

### 2. Bulk Actions Tidak Berfungsi
- Cek JavaScript console untuk error
- Pastikan semua checkbox terpilih dengan benar
- Cek permission file untuk write access

### 3. Export Tidak Berfungsi
- Pastikan PHP memiliki permission untuk write
- Cek memory limit untuk export data besar
- Pastikan browser mengizinkan download

## Future Enhancements

### 1. Email Notifications
- Kirim email otomatis saat pesanan dikonfirmasi
- Email reminder untuk pesanan pending

### 2. Payment Integration
- Integrasi dengan payment gateway
- Automatic payment verification

### 3. Reporting
- Laporan penjualan harian/bulanan
- Chart dan grafik untuk analisis
- Export ke format Excel/PDF

### 4. Advanced Features
- Auto-confirmation untuk pembayaran tertentu
- Schedule management untuk kapasitas
- Discount codes dan promotions 