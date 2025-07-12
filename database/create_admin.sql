-- Script untuk membuat akun admin default
-- Jalankan script ini di database zoo_management

USE zoo_management;

-- Hapus admin lama jika ada (opsional)
-- DELETE FROM users WHERE email = 'admin@zoo.com';

-- Buat akun admin baru
INSERT INTO users (username, email, phone, role, password) VALUES
('Admin Zoo', 'admin@zoo.com', '081234567890', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Password untuk akun admin di atas adalah: password

-- Tampilkan akun admin yang berhasil dibuat
SELECT id, username, email, role, created_at FROM users WHERE role = 'admin';

-- Catatan: 
-- Email: admin@zoo.com
-- Password: password
-- Role: admin 