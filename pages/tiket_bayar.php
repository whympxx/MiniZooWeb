<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

if (!isset($_GET['order_id']) && !isset($_POST['order_id'])) {
    header("Location: tiket.php");
    exit;
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : intval($_POST['order_id']);
$user_id = $_SESSION['user_id'];

// Ambil data pesanan
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

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode = $_POST['metode'] ?? '';
    if (!$metode) {
        $error = 'Pilih metode pembayaran.';
    } else {
        // Simulasi pembayaran sukses
        $update = $conn->prepare("UPDATE orders SET status = 'paid', metode_pembayaran = ?, waktu_bayar = NOW() WHERE id = ? AND user_id = ?");
        $update->bind_param("sii", $metode, $order_id, $user_id);
        if ($update->execute()) {
            $success = true;
            
            // Simulasi pengiriman email notifikasi
            sendEmailNotification($order['email'], $order['nama'], $order_id, $order['tanggal'], $order['jumlah'], $order['kategori'], $metode);
        } else {
            $error = 'Pembayaran gagal. Silakan coba lagi.';
        }
    }
}

// Fungsi simulasi pengiriman email
function sendEmailNotification($email, $nama, $order_id, $tanggal, $jumlah, $kategori, $metode) {
    // Simulasi pengiriman email
    $subject = "Konfirmasi Pembayaran Tiket Safari - Order #$order_id";
    $message = "
    <html>
    <body>
        <h2>🎉 Pembayaran Berhasil!</h2>
        <p>Halo $nama,</p>
        <p>Pembayaran tiket Safari Anda telah berhasil dikonfirmasi.</p>
        
        <h3>Detail Pesanan:</h3>
        <ul>
            <li><strong>Order ID:</strong> #$order_id</li>
            <li><strong>Tanggal Kunjungan:</strong> $tanggal</li>
            <li><strong>Jumlah Tiket:</strong> $jumlah ($kategori)</li>
            <li><strong>Metode Pembayaran:</strong> $metode</li>
        </ul>
        
        <p>Silakan tunjukkan email ini saat check-in di kebun binatang.</p>
        <p>Terima kasih telah memilih Kebun Binatang Safari!</p>
    </body>
    </html>
    ";
    
    // Simulasi: Simpan ke file log (dalam praktik nyata, gunakan library email seperti PHPMailer)
    $log_file = "email_logs.txt";
    $log_entry = date('Y-m-d H:i:s') . " - Email sent to: $email - Order: #$order_id\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Tiket - ZooDash</title>
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
                        'slide-up': 'slideUp 0.5s ease-out',
                        'bounce-in': 'bounceIn 0.8s ease-out',
                        'pulse-slow': 'pulse 3s infinite',
                        'float': 'float 3s ease-in-out infinite',
                        'shimmer': 'shimmer 2s linear infinite',
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
                        },
                        shimmer: {
                            '0%': { backgroundPosition: '-200% 0' },
                            '100%': { backgroundPosition: '200% 0' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-shadow {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
        }
        .payment-method:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        .bg-blue-25 {
            background-color: rgba(239, 246, 255, 0.5);
        }
        .success-checkmark {
            animation: bounceIn 0.8s ease-out;
        }
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }
        .floating-element {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        .floating-element:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
        .floating-element:nth-child(2) { top: 20%; right: 15%; animation-delay: 1s; }
        .floating-element:nth-child(3) { bottom: 30%; left: 20%; animation-delay: 2s; }
        .floating-element:nth-child(4) { bottom: 20%; right: 10%; animation-delay: 3s; }
    </style>
</head>
<body class="font-inter bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen relative overflow-x-hidden">
    <!-- Floating Background Elements -->
    <div class="floating-elements">
        <div class="floating-element text-6xl">🦁</div>
        <div class="floating-element text-5xl">🐘</div>
        <div class="floating-element text-4xl">🦒</div>
        <div class="floating-element text-5xl">🐼</div>
    </div>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-lg w-full animate-fade-in">
            <!-- Header Card -->
            <div class="glass-effect rounded-3xl card-shadow p-8 mb-6 animate-slide-up">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 text-white text-3xl mb-4 animate-bounce-in">
                        💳
                    </div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
                        Pembayaran Tiket
                    </h1>
                    <p class="text-gray-600 text-lg">
                        Selesaikan transaksi Anda dengan aman dan mudah
                    </p>
                </div>
            </div>

            <!-- Order Details Card -->
            <div class="glass-effect rounded-3xl card-shadow p-6 mb-6 animate-slide-up" style="animation-delay: 0.1s;">
                <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="mr-2">📋</span>
                    Detail Pesanan
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">Order ID</span>
                        <span class="text-blue-600 font-bold">#<?= $order_id ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">Nama</span>
                        <span class="text-gray-800 font-semibold"><?= htmlspecialchars($order['nama']) ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">Tanggal Kunjungan</span>
                        <span class="text-gray-800 font-semibold"><?= htmlspecialchars($order['tanggal']) ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">Jumlah Tiket</span>
                        <span class="text-gray-800 font-semibold"><?= htmlspecialchars($order['jumlah']) ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-600 font-medium">Kategori</span>
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                            <?= htmlspecialchars(ucfirst($order['kategori'])) ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if ($success): ?>
                <!-- Success State -->
                <div class="glass-effect rounded-3xl card-shadow p-8 text-center animate-bounce-in">
                    <div class="success-checkmark mb-6">
                        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-r from-green-400 to-emerald-500 text-white text-4xl">
                            ✅
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-green-800 mb-2">Pembayaran Berhasil!</h2>
                    <p class="text-green-600 mb-6">Tiket Anda sudah dikonfirmasi dan email notifikasi telah dikirim.</p>
                    
                    <div class="space-y-3">
                        <a href="tiket_export.php?order_id=<?= $order_id ?>" 
                           class="block w-full py-3 px-6 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold hover:from-blue-600 hover:to-blue-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                            📄 Export Tiket
                        </a>
                        <a href="tiket_riwayat.php" 
                           class="block w-full py-3 px-6 rounded-xl bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold hover:from-green-600 hover:to-green-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                            📊 Lihat Riwayat Pesanan
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Payment Form -->
                <div class="glass-effect rounded-3xl card-shadow p-8 animate-slide-up" style="animation-delay: 0.2s;">
                    <?php if ($error): ?>
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl animate-bounce-in">
                            <div class="flex items-center">
                                <span class="text-red-500 text-xl mr-3">⚠️</span>
                                <span class="text-red-700 font-medium"><?= htmlspecialchars($error) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form action="" method="post" class="space-y-6">
                        <input type="hidden" name="order_id" value="<?= $order_id ?>">
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                💳 Pilih Metode Pembayaran
                            </label>
                            <div class="grid gap-3">
                                <label class="payment-method cursor-pointer">
                                    <input type="radio" name="metode" value="Transfer Bank" class="sr-only" required>
                                    <div class="p-4 border-2 border-gray-200 rounded-xl hover:border-blue-300 transition-all duration-200 bg-white">
                                        <div class="flex items-center">
                                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full mr-3 flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-blue-500 rounded-full hidden"></div>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-semibold text-gray-800">Transfer Bank</div>
                                                <div class="text-sm text-gray-500">Transfer langsung ke rekening kami</div>
                                            </div>
                                            <span class="text-2xl">🏦</span>
                                        </div>
                                    </div>
                                </label>

                                <label class="payment-method cursor-pointer">
                                    <input type="radio" name="metode" value="E-Wallet" class="sr-only" required>
                                    <div class="p-4 border-2 border-gray-200 rounded-xl hover:border-blue-300 transition-all duration-200 bg-white">
                                        <div class="flex items-center">
                                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full mr-3 flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-blue-500 rounded-full hidden"></div>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-semibold text-gray-800">E-Wallet</div>
                                                <div class="text-sm text-gray-500">GoPay, OVO, DANA, LinkAja</div>
                                            </div>
                                            <span class="text-2xl">📱</span>
                                        </div>
                                    </div>
                                </label>

                                <label class="payment-method cursor-pointer">
                                    <input type="radio" name="metode" value="Kartu Kredit" class="sr-only" required>
                                    <div class="p-4 border-2 border-gray-200 rounded-xl hover:border-blue-300 transition-all duration-200 bg-white">
                                        <div class="flex items-center">
                                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full mr-3 flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-blue-500 rounded-full hidden"></div>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-semibold text-gray-800">Kartu Kredit</div>
                                                <div class="text-sm text-gray-500">Visa, Mastercard, JCB</div>
                                            </div>
                                            <span class="text-2xl">💳</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full py-4 px-6 rounded-xl text-white font-bold bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 transform focus:outline-none focus:ring-4 focus:ring-blue-300 shadow-lg transition-all duration-200 animate-pulse-slow opacity-50 cursor-not-allowed"
                                disabled>
                            <span class="flex items-center justify-center">
                                <span class="mr-2">💳</span>
                                Pilih Metode Pembayaran
                            </span>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="text-center mt-8 animate-slide-up" style="animation-delay: 0.3s;">
                <p class="text-gray-500 text-sm">
                    🔒 Pembayaran aman dengan enkripsi SSL
                </p>
                <p class="text-gray-400 text-xs mt-2">
                    &copy; <?= date('Y') ?> Kebun Binatang Safari. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Enhanced payment method selection functionality
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethods = document.querySelectorAll('.payment-method');
            const submitButton = document.querySelector('button[type="submit"]');
            const form = document.querySelector('form');

            // Initialize payment method selection
            function initializePaymentMethods() {
                paymentMethods.forEach(method => {
                    const radio = method.querySelector('input[type="radio"]');
                    const card = method.querySelector('.p-4');
                    const dot = method.querySelector('.w-2\\.5');
                    
                    // Add click event to the entire card
                    card.addEventListener('click', function() {
                        // Uncheck all radios first
                        paymentMethods.forEach(m => {
                            const r = m.querySelector('input[type="radio"]');
                            const c = m.querySelector('.p-4');
                            const d = m.querySelector('.w-2\\.5');
                            r.checked = false;
                            c.classList.remove('border-blue-300', 'bg-blue-50', 'ring-2', 'ring-blue-200');
                            c.classList.add('border-gray-200', 'bg-white');
                            d.classList.add('hidden');
                        });
                        
                        // Check current radio and update styling
                        radio.checked = true;
                        card.classList.remove('border-gray-200', 'bg-white');
                        card.classList.add('border-blue-300', 'bg-blue-50', 'ring-2', 'ring-blue-200');
                        dot.classList.remove('hidden');
                        
                        // Enable submit button
                        updateSubmitButton();
                    });
                    
                    // Add radio change event as backup
                    radio.addEventListener('change', function() {
                        updatePaymentMethodStyles();
                        updateSubmitButton();
                    });
                });
            }

            // Update payment method visual styles
            function updatePaymentMethodStyles() {
                paymentMethods.forEach(method => {
                    const radio = method.querySelector('input[type="radio"]');
                    const card = method.querySelector('.p-4');
                    const dot = method.querySelector('.w-2\\.5');
                    
                    if (radio.checked) {
                        card.classList.remove('border-gray-200', 'bg-white');
                        card.classList.add('border-blue-300', 'bg-blue-50', 'ring-2', 'ring-blue-200');
                        dot.classList.remove('hidden');
                    } else {
                        card.classList.remove('border-blue-300', 'bg-blue-50', 'ring-2', 'ring-blue-200');
                        card.classList.add('border-gray-200', 'bg-white');
                        dot.classList.add('hidden');
                    }
                });
            }

            // Update submit button state
            function updateSubmitButton() {
                const selectedMethod = document.querySelector('input[name="metode"]:checked');
                if (selectedMethod) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    submitButton.classList.add('hover:scale-105', 'cursor-pointer');
                } else {
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                    submitButton.classList.remove('hover:scale-105', 'cursor-pointer');
                }
            }

            // Form validation and submission
            if (form) {
                form.addEventListener('submit', function(e) {
                    const selectedMethod = document.querySelector('input[name="metode"]:checked');
                    
                    if (!selectedMethod) {
                        e.preventDefault();
                        showError('Silakan pilih metode pembayaran terlebih dahulu.');
                        return;
                    }
                    
                    // Show loading state
                    const button = this.querySelector('button[type="submit"]');
                    const originalContent = button.innerHTML;
                    button.innerHTML = `
                        <span class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses Pembayaran...
                        </span>
                    `;
                    button.disabled = true;
                    
                    // Store original content for potential restoration
                    button.dataset.originalContent = originalContent;
                });
            }

            // Show error message
            function showError(message) {
                // Remove existing error messages
                const existingError = document.querySelector('.error-message');
                if (existingError) {
                    existingError.remove();
                }
                
                // Create new error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message mb-6 p-4 bg-red-50 border border-red-200 rounded-xl animate-bounce-in';
                errorDiv.innerHTML = `
                    <div class="flex items-center">
                        <span class="text-red-500 text-xl mr-3">⚠️</span>
                        <span class="text-red-700 font-medium">${message}</span>
                    </div>
                `;
                
                // Insert error message before the form
                const formContainer = document.querySelector('.glass-effect.rounded-3xl.card-shadow.p-8');
                formContainer.insertBefore(errorDiv, formContainer.firstChild);
                
                // Auto-remove error after 5 seconds
                setTimeout(() => {
                    if (errorDiv.parentNode) {
                        errorDiv.remove();
                    }
                }, 5000);
            }

            // Initialize everything
            initializePaymentMethods();
            updateSubmitButton();
            
            // Add hover effects for better UX
            paymentMethods.forEach(method => {
                const card = method.querySelector('.p-4');
                const radio = method.querySelector('input[type="radio"]');
                
                card.addEventListener('mouseenter', function() {
                    if (!radio.checked) {
                        this.classList.add('border-blue-200', 'bg-blue-25');
                    }
                });
                
                card.addEventListener('mouseleave', function() {
                    if (!radio.checked) {
                        this.classList.remove('border-blue-200', 'bg-blue-25');
                    }
                });
            });
        });
    </script>
</body>
</html> 