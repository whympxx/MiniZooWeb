<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$orders = [];

// Ambil semua pesanan user dengan error handling
try {
    $query = "SELECT * FROM orders WHERE user_id = ? ORDER BY waktu_pesan DESC";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $orders = $result->fetch_all(MYSQLI_ASSOC);
        }
        $stmt->close();
    }
} catch (Exception $e) {
    // Log error silently and continue with empty orders
    error_log("Database error in tiket_riwayat.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - ZooDash</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'bounce-in': 'bounceIn 0.6s ease-out',
                        'pulse-slow': 'pulse 3s infinite',
                        'float': 'float 3s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        bounceIn: {
                            '0%': { transform: 'scale(0.3)', opacity: '0' },
                            '50%': { transform: 'scale(1.05)' },
                            '70%': { transform: 'scale(0.9)' },
                            '100%': { transform: 'scale(1)', opacity: '1' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .status-badge {
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease;
        }
        .status-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s;
        }
        .status-badge:hover::before {
            left: 100%;
        }
        .animate-delay-100 { animation-delay: 0.1s; }
        .animate-delay-200 { animation-delay: 0.2s; }
        .animate-delay-300 { animation-delay: 0.3s; }
        .animate-delay-400 { animation-delay: 0.4s; }
        .animate-delay-500 { animation-delay: 0.5s; }
        
        /* Fix for mobile responsiveness */
        @media (max-width: 768px) {
            .glass-effect {
                margin: 0 10px;
            }
            .card-hover:hover {
                transform: translateY(-2px) scale(1.01);
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col relative overflow-x-hidden">
    <!-- Animated Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float" style="animation-delay: 1s;"></div>
        <div class="absolute top-40 left-40 w-80 h-80 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float" style="animation-delay: 2s;"></div>
    </div>

    <!-- Header Navigation -->
    <nav class="relative z-10 glass-effect border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center animate-bounce-in">
                        <span class="text-white text-xl font-bold">🦁</span>
                    </div>
                    <h1 class="text-2xl font-bold text-white">ZooDash</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-white/80 hover:text-white transition-colors duration-200">Dashboard</a>
                    <a href="tiket.php" class="text-white/80 hover:text-white transition-colors duration-200">Pesan Tiket</a>
                    <a href="profil.php" class="text-white/80 hover:text-white transition-colors duration-200">Profil</a>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-all duration-200 transform hover:scale-105">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-4xl w-full space-y-8 animate-fade-in">
            <!-- Header Section -->
            <div class="text-center animate-slide-up">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 mb-6 animate-bounce-in">
                    <span class="text-3xl">📋</span>
                </div>
                <h2 class="text-4xl font-black text-white mb-4 tracking-tight">
                    Riwayat Pesanan Tiket
                </h2>
                <p class="text-xl text-white/80 max-w-2xl mx-auto leading-relaxed">
                    Berikut adalah daftar lengkap pesanan tiket Anda dengan status pembayaran yang terperinci
                </p>
            </div>

            <!-- Content Card -->
            <div class="glass-effect rounded-3xl shadow-2xl p-8 animate-slide-up animate-delay-200">
                <?php if (empty($orders)): ?>
                    <!-- Empty State -->
                    <div class="text-center py-16 animate-bounce-in animate-delay-300">
                        <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-r from-gray-400 to-gray-500 rounded-full flex items-center justify-center animate-pulse-slow">
                            <span class="text-4xl">🎫</span>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-4">Belum Ada Pesanan</h3>
                        <p class="text-white/70 mb-8 text-lg">Anda belum memiliki riwayat pesanan tiket. Mulai petualangan Anda sekarang!</p>
                        <a href="tiket.php" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold rounded-xl hover:from-purple-600 hover:to-pink-600 transition-all duration-300 transform hover:scale-105 hover:shadow-xl">
                            <span class="mr-2">🎯</span>
                            Pesan Tiket Sekarang
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Orders List -->
                    <div class="space-y-6">
                        <?php foreach ($orders as $index => $order): ?>
                            <?php 
                            // Validate and sanitize data
                            $order_id = isset($order['id']) ? (int)$order['id'] : 0;
                            $nama = isset($order['nama']) ? htmlspecialchars($order['nama']) : 'N/A';
                            $tanggal = isset($order['tanggal']) ? htmlspecialchars($order['tanggal']) : 'N/A';
                            $jumlah = isset($order['jumlah']) ? (int)$order['jumlah'] : 0;
                            $kategori = isset($order['kategori']) ? htmlspecialchars($order['kategori']) : 'N/A';
                            $status = isset($order['status']) ? $order['status'] : 'unknown';
                            $waktu_pesan = isset($order['waktu_pesan']) ? $order['waktu_pesan'] : '';
                            $waktu_bayar = isset($order['waktu_bayar']) ? $order['waktu_bayar'] : '';
                            $metode_pembayaran = isset($order['metode_pembayaran']) ? htmlspecialchars($order['metode_pembayaran']) : 'N/A';
                            
                            // Format dates safely
                            $waktu_pesan_formatted = $waktu_pesan ? date('d F Y', strtotime($waktu_pesan)) : 'N/A';
                            $waktu_pesan_time = $waktu_pesan ? date('d/m/Y H:i', strtotime($waktu_pesan)) : 'N/A';
                            $waktu_bayar_formatted = $waktu_bayar ? date('d/m/Y H:i', strtotime($waktu_bayar)) : 'N/A';
                            ?>
                            <div class="card-hover bg-white/10 rounded-2xl p-6 border border-white/20 animate-slide-up" style="animation-delay: <?= $index * 0.1 ?>s;">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl flex items-center justify-center">
                                            <span class="text-white font-bold text-lg">#<?= $order_id ?></span>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-white">Pesanan #<?= $order_id ?></h3>
                                            <p class="text-white/60 text-sm"><?= $waktu_pesan_formatted ?></p>
                                        </div>
                                    </div>
                                    <span class="status-badge px-4 py-2 rounded-full text-sm font-bold 
                                        <?= $status === 'paid' ? 'bg-gradient-to-r from-green-500 to-emerald-500 text-white' : 
                                           ($status === 'pending' ? 'bg-gradient-to-r from-yellow-500 to-orange-500 text-white' : 'bg-gradient-to-r from-red-500 to-pink-500 text-white') ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <div class="space-y-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center">
                                                <span class="text-blue-400">👤</span>
                                            </div>
                                            <div>
                                                <p class="text-white/60 text-sm">Nama</p>
                                                <p class="text-white font-semibold"><?= $nama ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center">
                                                <span class="text-green-400">📅</span>
                                            </div>
                                            <div>
                                                <p class="text-white/60 text-sm">Tanggal Kunjungan</p>
                                                <p class="text-white font-semibold"><?= $tanggal ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-purple-500/20 rounded-lg flex items-center justify-center">
                                                <span class="text-purple-400">🎫</span>
                                            </div>
                                            <div>
                                                <p class="text-white/60 text-sm">Jumlah Tiket</p>
                                                <p class="text-white font-semibold"><?= $jumlah ?> (<?= ucfirst($kategori) ?>)</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                                                <span class="text-yellow-400">⏰</span>
                                            </div>
                                            <div>
                                                <p class="text-white/60 text-sm">Waktu Pesan</p>
                                                <p class="text-white font-semibold"><?= $waktu_pesan_time ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($status === 'paid' && $waktu_bayar): ?>
                                    <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4 mb-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center">
                                                    <span class="text-green-400">💳</span>
                                                </div>
                                                <div>
                                                    <p class="text-white/60 text-sm">Metode Pembayaran</p>
                                                    <p class="text-white font-semibold"><?= $metode_pembayaran ?></p>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center">
                                                    <span class="text-green-400">✅</span>
                                                </div>
                                                <div>
                                                    <p class="text-white/60 text-sm">Waktu Bayar</p>
                                                    <p class="text-white font-semibold"><?= $waktu_bayar_formatted ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Action Buttons -->
                                <div class="flex flex-wrap gap-3">
                                    <?php if ($status === 'pending'): ?>
                                        <a href="tiket_bayar.php?order_id=<?= $order_id ?>" 
                                           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-yellow-500 to-orange-500 text-white font-bold rounded-xl hover:from-yellow-600 hover:to-orange-600 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                                            <span class="mr-2">💳</span>
                                            Lanjutkan Pembayaran
                                        </a>
                                    <?php elseif ($status === 'paid'): ?>
                                        <a href="tiket_export.php?order_id=<?= $order_id ?>" 
                                           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-xl hover:from-blue-600 hover:to-purple-600 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                                            <span class="mr-2">📄</span>
                                            Export Tiket
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Bottom Action -->
                    <div class="text-center mt-8 animate-slide-up animate-delay-500">
                        <a href="tiket.php" 
                           class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold rounded-xl hover:from-purple-600 hover:to-pink-600 transition-all duration-300 transform hover:scale-105 hover:shadow-xl">
                            <span class="mr-2">➕</span>
                            Pesan Tiket Baru
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="text-center animate-slide-up animate-delay-500">
                <p class="text-white/50 text-sm">
                    &copy; <?= date('Y') ?> Kebun Binatang Safari - Semua hak dilindungi
                </p>
            </div>
        </div>
    </div>

    <!-- Loading Animation -->
    <script>
        // Add loading animation with error handling
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const cards = document.querySelectorAll('.card-hover');
                cards.forEach((card, index) => {
                    if (card && card.style) {
                        card.style.animationDelay = `${index * 0.1}s`;
                    }
                });

                // Add hover effects for status badges
                const statusBadges = document.querySelectorAll('.status-badge');
                statusBadges.forEach(badge => {
                    if (badge) {
                        badge.addEventListener('mouseenter', function() {
                            this.style.transform = 'scale(1.05)';
                        });
                        badge.addEventListener('mouseleave', function() {
                            this.style.transform = 'scale(1)';
                        });
                    }
                });
            } catch (error) {
                console.error('Error in animation script:', error);
            }
        });

        // Add error handling for broken images
        document.addEventListener('error', function(e) {
            if (e.target.tagName === 'IMG') {
                e.target.style.display = 'none';
            }
        }, true);
    </script>
</body>
</html> 