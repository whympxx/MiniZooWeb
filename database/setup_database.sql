-- Membuat database zoo_management
CREATE DATABASE IF NOT EXISTS zoo_management;

-- Menggunakan database zoo_management
USE zoo_management;

-- Membuat tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Menambahkan beberapa data contoh (opsional)
INSERT INTO users (username, email, phone, role, password) VALUES
('admin', 'admin@example.com', '08123456789', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('user1', 'user1@example.com', '08987654321', 'user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Password untuk kedua akun di atas adalah: password

-- Tabel untuk menyimpan pesanan tiket
CREATE TABLE IF NOT EXISTS orders (
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
