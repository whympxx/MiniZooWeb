-- Sample data untuk tabel orders
-- Pastikan user dengan ID 2 dan 3 sudah ada di tabel users

INSERT INTO orders (user_id, nama, email, tanggal, jumlah, kategori, status, waktu_pesan) VALUES
(2, 'John Doe', 'john@example.com', '2025-01-15', 2, 'dewasa', 'pending', '2025-01-10 10:30:00'),
(2, 'John Doe', 'john@example.com', '2025-01-20', 1, 'anak', 'paid', '2025-01-12 14:20:00'),
(3, 'Wahyu Marga Pratama', 'wahyu77sky@gmail.com', '2025-01-18', 4, 'keluarga', 'pending', '2025-01-13 09:15:00'),
(3, 'Wahyu Marga Pratama', 'wahyu77sky@gmail.com', '2025-01-25', 2, 'dewasa', 'paid', '2025-01-14 16:45:00'),
(2, 'John Doe', 'john@example.com', '2025-01-22', 3, 'anak', 'failed', '2025-01-15 11:30:00'),
(3, 'Wahyu Marga Pratama', 'wahyu77sky@gmail.com', '2025-01-28', 1, 'dewasa', 'pending', '2025-01-16 13:20:00'),
(2, 'John Doe', 'john@example.com', '2025-01-30', 2, 'keluarga', 'paid', '2025-01-17 15:10:00'),
(3, 'Wahyu Marga Pratama', 'wahyu77sky@gmail.com', '2025-02-01', 1, 'anak', 'pending', '2025-01-18 08:45:00'),
(2, 'John Doe', 'john@example.com', '2025-02-05', 3, 'dewasa', 'paid', '2025-01-19 12:30:00'),
(3, 'Wahyu Marga Pratama', 'wahyu77sky@gmail.com', '2025-02-08', 2, 'keluarga', 'pending', '2025-01-20 17:15:00');

-- Update waktu_bayar untuk pesanan yang sudah paid
UPDATE orders SET waktu_bayar = DATE_ADD(waktu_pesan, INTERVAL 2 HOUR) WHERE status = 'paid'; 