# Admin Settings System

## Overview
Sistem pengaturan admin yang lengkap dan profesional untuk mengelola konfigurasi aplikasi Kebun Binatang Surabaya. Fitur ini menyediakan antarmuka yang user-friendly dengan animasi yang menarik dan fungsionalitas yang komprehensif.

## Fitur Utama

### 1. Pengaturan Harga Tiket
- **Harga Tiket Dewasa**: Rp 50.000 (default)
- **Harga Tiket Anak-anak**: Rp 30.000 (default)
- **Harga Tiket Keluarga**: Rp 120.000 (default)
- Validasi input real-time
- Animasi hover yang menarik
- Penyimpanan otomatis ke session

### 2. Pengaturan Sistem
- **Nama Kebun Binatang**: Konfigurasi nama institusi
- **Alamat**: Alamat lengkap kebun binatang
- **Telepon**: Nomor kontak resmi
- **Email**: Email kontak resmi
- **Jam Operasional**: Jam buka dan tutup
- **Maksimal Tiket/Hari**: Batasan jumlah tiket per hari

### 3. Pengaturan Notifikasi
- **Notifikasi Email**: Toggle on/off
- **Notifikasi SMS**: Toggle on/off
- **Konfirmasi Otomatis**: Toggle untuk konfirmasi otomatis pesanan
- **Email Notifikasi**: Email untuk menerima notifikasi

### 4. Quick Actions
- **Backup Database**: Membuat backup database otomatis
- **Kelola Admin**: Manajemen akun administrator
- **Log Sistem**: Melihat dan membersihkan log sistem
- **Keamanan**: Pengaturan keamanan sistem

## File yang Dibuat

### 1. `admin_settings.php`
File utama halaman pengaturan dengan fitur:
- Interface yang responsif dan modern
- Animasi CSS yang smooth
- Validasi form real-time
- Notifikasi sukses/error
- Modal untuk quick actions

### 2. `admin_settings_actions.php`
Handler untuk AJAX requests dengan fitur:
- Backup database otomatis
- Test email functionality
- Clear system logs
- Export settings
- Get system information

### 3. `admin-settings.css`
File CSS khusus dengan animasi:
- Fade in/out animations
- Hover effects
- Toggle switch styling
- Responsive design
- Dark mode support

## Cara Penggunaan

### 1. Akses Halaman Pengaturan
1. Login sebagai admin
2. Klik tombol "Pengaturan" di navigation bar
3. Atau klik tombol "Settings" di halaman manajemen tiket

### 2. Mengubah Harga Tiket
1. Masuk ke section "Harga Tiket"
2. Ubah nilai pada input field yang diinginkan
3. Klik "Simpan Harga Tiket"
4. Sistem akan menampilkan notifikasi sukses

### 3. Mengubah Pengaturan Sistem
1. Masuk ke section "Pengaturan Sistem"
2. Isi semua field yang diperlukan
3. Klik "Simpan Pengaturan Sistem"
4. Pengaturan akan tersimpan

### 4. Mengatur Notifikasi
1. Masuk ke section "Pengaturan Notifikasi"
2. Toggle switch untuk mengaktifkan/menonaktifkan fitur
3. Masukkan email notifikasi
4. Klik "Simpan Pengaturan Notifikasi"

### 5. Quick Actions
1. **Backup Database**: Klik tombol "Backup" untuk membuat backup
2. **Log Sistem**: Klik "Lihat Log" untuk melihat log aktivitas
3. **Kelola Admin**: Klik "Kelola" untuk manajemen admin
4. **Keamanan**: Klik "Keamanan" untuk pengaturan keamanan

## Animasi dan Efek Visual

### 1. Card Animations
- Slide in dari bawah dengan delay bertahap
- Hover effect dengan transform dan shadow
- Scale animation pada focus

### 2. Form Interactions
- Input scaling pada focus
- Smooth transitions
- Validation feedback dengan shake animation

### 3. Toggle Switches
- Smooth sliding animation
- Color transition
- Shadow effects

### 4. Buttons
- Hover scale effect
- Ripple effect pada click
- Loading spinner animation

### 5. Notifications
- Slide in dari kanan
- Auto-hide setelah 3 detik
- Color-coded (success, error, info, warning)

## Keamanan

### 1. Authentication
- Validasi session admin
- Redirect otomatis jika bukan admin
- CSRF protection

### 2. Input Validation
- Server-side validation
- Client-side validation
- SQL injection prevention
- XSS protection

### 3. File Operations
- Directory traversal protection
- File permission checks
- Secure file naming

## Database Integration

### 1. Session Storage
Pengaturan disimpan dalam session untuk performa:
```php
$_SESSION['ticket_prices'] = [
    'dewasa' => 50000,
    'anak' => 30000,
    'keluarga' => 120000
];
```

### 2. Backup System
- Otomatis membuat direktori backup
- Nama file dengan timestamp
- Error handling yang robust

### 3. Log Management
- Email logs
- System logs
- Access logs
- Clear logs functionality

## Responsive Design

### 1. Mobile Optimization
- Grid layout yang adaptif
- Touch-friendly buttons
- Optimized spacing

### 2. Tablet Support
- Medium breakpoint optimization
- Touch interactions
- Readable text sizes

### 3. Desktop Experience
- Full feature access
- Hover effects
- Keyboard navigation

## Browser Compatibility

### 1. Modern Browsers
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### 2. Features Used
- CSS Grid
- Flexbox
- CSS Animations
- ES6+ JavaScript
- Fetch API

## Performance Optimization

### 1. CSS Optimization
- Minimal CSS dengan Tailwind
- Custom animations yang efisien
- Lazy loading untuk non-critical CSS

### 2. JavaScript Optimization
- Event delegation
- Debounced search
- Efficient DOM manipulation

### 3. Server Optimization
- Minimal database queries
- Session-based caching
- Efficient file operations

## Troubleshooting

### 1. Common Issues

#### Backup Database Gagal
- Pastikan mysqldump tersedia di server
- Check file permissions untuk direktori backup
- Verify database credentials

#### Email Test Gagal
- Check SMTP configuration
- Verify email server settings
- Test dengan email yang valid

#### Log Tidak Muncul
- Check file permissions
- Verify log file exists
- Check file path

### 2. Debug Mode
Untuk debugging, tambahkan di awal file:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Future Enhancements

### 1. Planned Features
- Database settings persistence
- Advanced email configuration
- User activity monitoring
- Advanced security settings
- Backup scheduling
- Email templates management

### 2. Integration Possibilities
- Third-party email services
- Cloud backup integration
- Advanced logging systems
- Monitoring dashboards

## Support

Untuk bantuan teknis atau pertanyaan, silakan hubungi:
- Email: admin@zoosurabaya.com
- Phone: +62 31 1234567

## License

Sistem ini dikembangkan untuk Kebun Binatang Surabaya. Semua hak cipta dilindungi. 