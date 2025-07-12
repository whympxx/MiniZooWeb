<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/Login.php');
    exit();
}

// Get admin data
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$admin = $stmt->fetch();

// Handle settings updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_ticket_prices':
                // Update ticket prices in session (in real app, this would be stored in database)
                $_SESSION['ticket_prices'] = [
                    'dewasa' => (int)$_POST['price_dewasa'],
                    'anak' => (int)$_POST['price_anak'],
                    'keluarga' => (int)$_POST['price_keluarga']
                ];
                $success_message = "Harga tiket berhasil diperbarui!";
                break;
                
            case 'update_system_settings':
                $_SESSION['system_settings'] = [
                    'maintenance_mode' => isset($_POST['maintenance_mode']),
                    'auto_confirm_orders' => isset($_POST['auto_confirm_orders']),
                    'max_tickets_per_order' => (int)$_POST['max_tickets_per_order'],
                    'zoo_opening_hours' => $_POST['zoo_opening_hours'],
                    'zoo_closing_hours' => $_POST['zoo_closing_hours']
                ];
                $success_message = "Pengaturan sistem berhasil diperbarui!";
                break;
                
            case 'update_notification_settings':
                $_SESSION['notification_settings'] = [
                    'email_notifications' => isset($_POST['email_notifications']),
                    'sms_notifications' => isset($_POST['sms_notifications']),
                    'order_confirmation_email' => isset($_POST['order_confirmation_email']),
                    'order_rejection_email' => isset($_POST['order_rejection_email']),
                    'daily_report_email' => isset($_POST['daily_report_email'])
                ];
                $success_message = "Pengaturan notifikasi berhasil diperbarui!";
                break;
        }
    }
}

// Initialize default settings if not set
if (!isset($_SESSION['ticket_prices'])) {
    $_SESSION['ticket_prices'] = [
        'dewasa' => 50000,
        'anak' => 30000,
        'keluarga' => 120000
    ];
}

if (!isset($_SESSION['system_settings'])) {
    $_SESSION['system_settings'] = [
        'maintenance_mode' => false,
        'auto_confirm_orders' => false,
        'max_tickets_per_order' => 10,
        'zoo_opening_hours' => '08:00',
        'zoo_closing_hours' => '17:00'
    ];
}

if (!isset($_SESSION['notification_settings'])) {
    $_SESSION['notification_settings'] = [
        'email_notifications' => true,
        'sms_notifications' => false,
        'order_confirmation_email' => true,
        'order_rejection_email' => true,
        'daily_report_email' => false
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/admin-tailwind.css">
    <link rel="stylesheet" href="../assets/css/admin-settings.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b-4 border-indigo-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-alt text-3xl text-indigo-600"></i>
                    </div>
                    <div class="hidden md:block ml-4">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="admin_dashboard.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                            <a href="admin_analytics.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Analytics</a>
                            <a href="admin_tiket_management.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Manajemen Tiket</a>
                            <a href="#" class="text-indigo-600 px-3 py-2 rounded-md text-sm font-medium border-b-2 border-indigo-600">Pengaturan</a>
                            <a href="../pages/dashboard.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Zoo Dashboard</a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700 text-sm">Welcome, <?php echo htmlspecialchars($admin['username']); ?></span>
                        <a href="../pages/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2 admin-title">
                <i class="fas fa-cog text-indigo-500 mr-3"></i>Pengaturan Sistem
            </h1>
            <p class="text-gray-600 text-lg">Kelola pengaturan sistem dan konfigurasi aplikasi</p>
        </div>

        <!-- Success Message -->
        <?php if (isset($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 animate-fade-in">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Settings Navigation -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex flex-wrap gap-4">
                <button onclick="showSection('ticket-prices')" class="settings-nav-btn active bg-indigo-600 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 hover:bg-indigo-700 transform hover:scale-105">
                    <i class="fas fa-ticket-alt mr-2"></i>Harga Tiket
                </button>
                <button onclick="showSection('system-settings')" class="settings-nav-btn bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium transition-all duration-300 hover:bg-gray-300 transform hover:scale-105">
                    <i class="fas fa-cogs mr-2"></i>Pengaturan Sistem
                </button>
                <button onclick="showSection('notification-settings')" class="settings-nav-btn bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium transition-all duration-300 hover:bg-gray-300 transform hover:scale-105">
                    <i class="fas fa-bell mr-2"></i>Notifikasi
                </button>
                <button onclick="showSection('backup-restore')" class="settings-nav-btn bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium transition-all duration-300 hover:bg-gray-300 transform hover:scale-105">
                    <i class="fas fa-database mr-2"></i>Backup & Restore
                </button>
            </div>
        </div>

        <!-- Ticket Prices Section -->
        <div id="ticket-prices" class="settings-section">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-ticket-alt text-green-500 mr-3"></i>
                        Pengaturan Harga Tiket
                    </h2>
                    <p class="text-gray-600 mt-1">Atur harga tiket untuk berbagai kategori pengunjung</p>
                </div>
                
                <form method="POST" class="p-6">
                    <input type="hidden" name="action" value="update_ticket_prices">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="setting-card">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user mr-2 text-blue-500"></i>Harga Tiket Dewasa
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                <input type="number" name="price_dewasa" value="<?php echo $_SESSION['ticket_prices']['dewasa']; ?>" 
                                       class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300">
                            </div>
                        </div>
                        
                        <div class="setting-card">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-child mr-2 text-green-500"></i>Harga Tiket Anak
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                <input type="number" name="price_anak" value="<?php echo $_SESSION['ticket_prices']['anak']; ?>" 
                                       class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300">
                            </div>
                        </div>
                        
                        <div class="setting-card">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-users mr-2 text-purple-500"></i>Harga Tiket Keluarga
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                <input type="number" name="price_keluarga" value="<?php echo $_SESSION['ticket_prices']['keluarga']; ?>" 
                                       class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- System Settings Section -->
        <div id="system-settings" class="settings-section hidden">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-cogs text-blue-500 mr-3"></i>
                        Pengaturan Sistem
                    </h2>
                    <p class="text-gray-600 mt-1">Konfigurasi pengaturan umum sistem</p>
                </div>
                
                <form method="POST" class="p-6">
                    <input type="hidden" name="action" value="update_system_settings">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="setting-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-tools mr-2 text-orange-500"></i>Mode Maintenance
                                    </label>
                                    <p class="text-sm text-gray-500">Aktifkan mode maintenance untuk sementara menonaktifkan sistem</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="maintenance_mode" <?php echo $_SESSION['system_settings']['maintenance_mode'] ? 'checked' : ''; ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="setting-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-robot mr-2 text-green-500"></i>Auto Konfirmasi Order
                                    </label>
                                    <p class="text-sm text-gray-500">Otomatis konfirmasi pesanan tanpa perlu approval manual</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="auto_confirm_orders" <?php echo $_SESSION['system_settings']['auto_confirm_orders'] ? 'checked' : ''; ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="setting-card">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-ticket-alt mr-2 text-purple-500"></i>Maksimal Tiket per Order
                            </label>
                            <input type="number" name="max_tickets_per_order" value="<?php echo $_SESSION['system_settings']['max_tickets_per_order']; ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300">
                        </div>
                        
                        <div class="setting-card">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-clock mr-2 text-blue-500"></i>Jam Buka Zoo
                            </label>
                            <input type="time" name="zoo_opening_hours" value="<?php echo $_SESSION['system_settings']['zoo_opening_hours']; ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300">
                        </div>
                        
                        <div class="setting-card">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-clock mr-2 text-red-500"></i>Jam Tutup Zoo
                            </label>
                            <input type="time" name="zoo_closing_hours" value="<?php echo $_SESSION['system_settings']['zoo_closing_hours']; ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300">
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notification Settings Section -->
        <div id="notification-settings" class="settings-section hidden">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-bell text-yellow-500 mr-3"></i>
                        Pengaturan Notifikasi
                    </h2>
                    <p class="text-gray-600 mt-1">Atur preferensi notifikasi sistem</p>
                </div>
                
                <form method="POST" class="p-6">
                    <input type="hidden" name="action" value="update_notification_settings">
                    
                    <div class="space-y-6">
                        <div class="setting-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-envelope mr-2 text-blue-500"></i>Notifikasi Email
                                    </label>
                                    <p class="text-sm text-gray-500">Aktifkan notifikasi melalui email</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="email_notifications" <?php echo $_SESSION['notification_settings']['email_notifications'] ? 'checked' : ''; ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="setting-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-sms mr-2 text-green-500"></i>Notifikasi SMS
                                    </label>
                                    <p class="text-sm text-gray-500">Aktifkan notifikasi melalui SMS</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="sms_notifications" <?php echo $_SESSION['notification_settings']['sms_notifications'] ? 'checked' : ''; ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="setting-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-check-circle mr-2 text-green-500"></i>Email Konfirmasi Order
                                    </label>
                                    <p class="text-sm text-gray-500">Kirim email saat order dikonfirmasi</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="order_confirmation_email" <?php echo $_SESSION['notification_settings']['order_confirmation_email'] ? 'checked' : ''; ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="setting-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-times-circle mr-2 text-red-500"></i>Email Penolakan Order
                                    </label>
                                    <p class="text-sm text-gray-500">Kirim email saat order ditolak</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="order_rejection_email" <?php echo $_SESSION['notification_settings']['order_rejection_email'] ? 'checked' : ''; ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="setting-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-chart-bar mr-2 text-purple-500"></i>Laporan Harian Email
                                    </label>
                                    <p class="text-sm text-gray-500">Kirim laporan harian melalui email</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="daily_report_email" <?php echo $_SESSION['notification_settings']['daily_report_email'] ? 'checked' : ''; ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Backup & Restore Section -->
        <div id="backup-restore" class="settings-section hidden">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-database text-purple-500 mr-3"></i>
                        Backup & Restore
                    </h2>
                    <p class="text-gray-600 mt-1">Kelola backup dan restore database</p>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="setting-card">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-download mr-2 text-green-500"></i>Buat Backup
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">Buat backup database untuk keamanan data</p>
                            <button onclick="createBackup()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">
                                <i class="fas fa-download mr-2"></i>Buat Backup
                            </button>
                        </div>
                        
                        <div class="setting-card">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-upload mr-2 text-blue-500"></i>Restore Database
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">Restore database dari file backup</p>
                            <input type="file" id="backupFile" accept=".sql" class="hidden">
                            <button onclick="document.getElementById('backupFile').click()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">
                                <i class="fas fa-upload mr-2"></i>Pilih File
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-history mr-2 text-gray-500"></i>Riwayat Backup
                        </h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Fitur ini akan menampilkan riwayat backup yang tersedia</p>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-robot mr-2 text-green-500"></i>Auto Konfirmasi Cepat
                        </h3>
                        <div class="bg-green-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-4">Konfirmasi otomatis semua pesanan yang menunggu konfirmasi</p>
                            <button onclick="triggerAutoConfirmation()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">
                                <i class="fas fa-play mr-2"></i>Jalankan Auto Konfirmasi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.settings-section').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show selected section
            document.getElementById(sectionId).classList.remove('hidden');
            
            // Update navigation buttons
            document.querySelectorAll('.settings-nav-btn').forEach(btn => {
                btn.classList.remove('bg-indigo-600', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });
            
            // Highlight active button
            event.target.classList.remove('bg-gray-200', 'text-gray-700');
            event.target.classList.add('bg-indigo-600', 'text-white');
        }
        
        function createBackup() {
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
            button.disabled = true;
            
            // Send backup request
            const formData = new FormData();
            formData.append('action', 'create_backup');
            
            fetch('database_backup.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Backup berhasil dibuat!\n\nFile: ${data.filename}\nSize: ${data.size}`);
                    
                    // Auto download the backup
                    setTimeout(() => {
                        window.open(data.download_url, '_blank');
                    }, 1000);
                } else {
                    alert('Gagal membuat backup: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            })
            .finally(() => {
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
        
        function triggerAutoConfirmation() {
            if (!confirm('Apakah Anda yakin ingin mengkonfirmasi otomatis semua pesanan yang menunggu? Tindakan ini akan mengkonfirmasi semua pesanan pending.')) {
                return;
            }
            
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            button.disabled = true;
            
            // Send auto-confirmation request
            const formData = new FormData();
            formData.append('action', 'auto_confirm_all');
            
            fetch('auto_confirm_orders.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Auto konfirmasi berhasil!\n\nDikonfirmasi: ${data.confirmed}\nGagal: ${data.failed}\nTotal: ${data.total}`);
                } else {
                    alert('Gagal melakukan auto konfirmasi: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            })
            .finally(() => {
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
        
        // Auto-hide success message after 5 seconds
        setTimeout(() => {
            const successMessage = document.querySelector('.animate-fade-in');
            if (successMessage) {
                successMessage.style.opacity = '0';
                setTimeout(() => {
                    successMessage.remove();
                }, 300);
            }
        }, 5000);
    </script>
</body>
</html> 