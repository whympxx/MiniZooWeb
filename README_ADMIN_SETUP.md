# 🦁 Zoo Management System - Admin Setup Guide

## Cara Membuat Akun Admin

Ada beberapa cara untuk membuat akun admin di Zoo Management System:

### 🚀 Cara 1: Menggunakan Script Otomatis (Recommended)

1. **Jalankan script setup admin:**
   ```
   http://localhost/Tugas13/setup_admin.php
   ```

2. **Script akan otomatis membuat akun admin dengan kredensial:**
   - Email: `admin@zoo.com`
   - Password: `password`
   - Role: `admin`

3. **Login ke sistem:**
   - Buka: `http://localhost/Tugas13/pages/Login.php`
   - Masukkan kredensial admin
   - Anda akan diarahkan ke admin dashboard

### 🎯 Cara 2: Menggunakan Form Web Interface

1. **Buka halaman create admin:**
   ```
   http://localhost/Tugas13/create_admin_account.php
   ```

2. **Isi form dengan data admin:**
   - Username: Nama admin
   - Email: Email admin
   - Phone: Nomor telepon
   - Password: Password admin
   - Confirm Password: Konfirmasi password

3. **Klik "Buat Akun Admin"**

4. **Login dengan akun yang baru dibuat**

### 📊 Cara 3: Menggunakan SQL Manual

1. **Buka phpMyAdmin:**
   ```
   http://localhost/phpmyadmin
   ```

2. **Pilih database `zoo_management`**

3. **Jalankan query SQL:**
   ```sql
   INSERT INTO users (username, email, phone, role, password) VALUES
   ('Admin Zoo', 'admin@zoo.com', '081234567890', 'admin', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
   ```

4. **Password untuk query di atas adalah: `password`**

### 🔧 Cara 4: Menggunakan File SQL

1. **Buka file:**
   ```
   database/create_admin.sql
   ```

2. **Jalankan script SQL di phpMyAdmin atau MySQL client**

## 📋 Akun Admin Default

Jika Anda menjalankan `database/setup_database.sql`, sudah ada akun admin default:

- **Email:** `admin@example.com`
- **Password:** `password`
- **Role:** `admin`

## 🔐 Keamanan

⚠️ **PENTING:** Setelah login pertama kali, segera ubah password default untuk keamanan!

### Cara Mengubah Password Admin:

1. **Login sebagai admin**
2. **Buka halaman profil admin**
3. **Klik "Ubah Password"**
4. **Masukkan password baru**
5. **Simpan perubahan**

## 🌐 URL Penting

- **User Login:** `http://localhost/Tugas13/pages/Login.php`
- **Admin Dashboard:** `http://localhost/Tugas13/admin/admin_dashboard.php`
- **Create Admin:** `http://localhost/Tugas13/create_admin_account.php`
- **Setup Admin:** `http://localhost/Tugas13/setup_admin.php`

## 🛠️ Troubleshooting

### Jika Admin Tidak Bisa Login:

1. **Cek database connection:**
   ```
   http://localhost/Tugas13/test_database.php
   ```

2. **Pastikan tabel users ada:**
   ```sql
   SHOW TABLES LIKE 'users';
   ```

3. **Cek akun admin:**
   ```sql
   SELECT username, email, role FROM users WHERE role = 'admin';
   ```

4. **Reset password admin:**
   ```sql
   UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
   WHERE email = 'admin@zoo.com';
   ```

### Jika Database Error:

1. **Pastikan XAMPP MySQL running**
2. **Cek konfigurasi di `config.php`**
3. **Jalankan `database/setup_database.sql`**
4. **Pastikan database `zoo_management` ada**

## 📞 Support

Jika mengalami masalah, cek:
- File log di folder `logs/`
- Error log XAMPP
- Console browser untuk error JavaScript

---

**🎉 Selamat! Admin account Anda siap digunakan!** 