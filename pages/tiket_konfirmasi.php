<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

if (!isset($_GET['order_id'])) {
    header("Location: tiket.php");
    exit;
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo '<div class="text-red-600 text-center mt-8">Pesanan tidak ditemukan.</div>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pesanan - ZooDash</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'slide-up': 'slideUp 0.8s ease-out',
                        'bounce-in': 'bounceIn 0.8s ease-out',
                        'pulse-slow': 'pulse 3s infinite',
                        'float': 'float 3s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(30px)', opacity: '0' },
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
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .zoo-gradient-bg {
            background: linear-gradient(135deg, #fef9c3 0%, #bbf7d0 50%, #bae6fd 100%);
        }
        .success-gradient {
            background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
        }
        .card-shadow {
            box-shadow: 0 20px 25px -5px rgba(34, 197, 94, 0.1), 0 10px 10px -5px rgba(34, 197, 94, 0.04);
        }
        .hover-lift {
            transition: all 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(34, 197, 94, 0.25);
        }
    </style>
</head>
<body class="min-h-screen zoo-gradient-bg relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-green-200/20 rounded-full blur-3xl animate-float"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-200/20 rounded-full blur-3xl animate-float" style="animation-delay: 1.5s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-yellow-200/10 rounded-full blur-3xl animate-pulse-slow"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-2xl">
            <!-- Header Card -->
            <div class="glass-effect rounded-3xl p-8 mb-6 animate-fade-in card-shadow">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full mb-6 animate-bounce-in shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-green-900 mb-2 animate-slide-up">Konfirmasi Pesanan</h1>
                    <p class="text-green-700 text-lg animate-slide-up" style="animation-delay: 0.2s;">
                        Periksa detail pesanan Anda sebelum melanjutkan ke pembayaran
                    </p>
                </div>
            </div>

            <!-- Order Details Card -->
            <div class="glass-effect rounded-3xl p-8 animate-slide-up card-shadow" style="animation-delay: 0.3s;">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-green-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Detail Pesanan
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex items-center p-4 bg-green-50/80 rounded-xl backdrop-blur-sm border border-green-200/50">
                                <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-green-600 text-sm">Nama</p>
                                    <p class="text-green-900 font-medium"><?= htmlspecialchars($order['nama']) ?></p>
                                </div>
                            </div>

                            <div class="flex items-center p-4 bg-green-50/80 rounded-xl backdrop-blur-sm border border-green-200/50">
                                <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-green-600 text-sm">Email</p>
                                    <p class="text-green-900 font-medium"><?= htmlspecialchars($order['email']) ?></p>
                                </div>
                            </div>

                            <div class="flex items-center p-4 bg-green-50/80 rounded-xl backdrop-blur-sm border border-green-200/50">
                                <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-green-600 text-sm">Tanggal Kunjungan</p>
                                    <p class="text-green-900 font-medium"><?= htmlspecialchars($order['tanggal']) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center p-4 bg-green-50/80 rounded-xl backdrop-blur-sm border border-green-200/50">
                                <div class="w-10 h-10 bg-orange-500/20 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-green-600 text-sm">Jumlah Tiket</p>
                                    <p class="text-green-900 font-medium"><?= htmlspecialchars($order['jumlah']) ?> tiket</p>
                                </div>
                            </div>

                            <div class="flex items-center p-4 bg-green-50/80 rounded-xl backdrop-blur-sm border border-green-200/50">
                                <div class="w-10 h-10 bg-indigo-500/20 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-green-600 text-sm">Kategori</p>
                                    <p class="text-green-900 font-medium"><?= htmlspecialchars(ucfirst($order['kategori'])) ?></p>
                                </div>
                            </div>

                            <div class="flex items-center p-4 bg-green-50/80 rounded-xl backdrop-blur-sm border border-green-200/50">
                                <div class="w-10 h-10 bg-yellow-500/20 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-green-600 text-sm">Status</p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                        <?= htmlspecialchars(ucfirst($order['status'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-green-200/50">
                    <a href="tiket.php" class="flex-1 px-6 py-3 bg-green-100/80 hover:bg-green-200/80 text-green-800 font-medium rounded-xl transition-all duration-300 hover-lift backdrop-blur-sm flex items-center justify-center border border-green-200/50">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali
                    </a>
                    
                    <form action="tiket_bayar.php" method="get" class="flex-1">
                        <input type="hidden" name="order_id" value="<?= $order_id ?>">
                        <button type="submit" class="w-full px-6 py-3 success-gradient hover:from-green-500 hover:to-emerald-600 text-white font-semibold rounded-xl transition-all duration-300 hover-lift shadow-lg flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Lanjut ke Pembayaran
                        </button>
                    </form>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 animate-fade-in" style="animation-delay: 0.6s;">
                <p class="text-green-600/80 text-sm">
                    &copy; <?= date('Y') ?> Kebun Binatang Safari. Semua hak dilindungi.
                </p>
            </div>
        </div>
    </div>

    <!-- Loading Animation -->
    <script>
        // Add loading animation
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.animate-fade-in, .animate-slide-up, .animate-bounce-in');
            elements.forEach((el, index) => {
                el.style.animationDelay = `${index * 0.1}s`;
            });
        });

        // Add hover effects for interactive elements
        document.querySelectorAll('.hover-lift').forEach(element => {
            element.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            element.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html> 