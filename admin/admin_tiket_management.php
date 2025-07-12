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

// Handle order actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['order_id'])) {
        $order_id = $_POST['order_id'];
        
        switch ($_POST['action']) {
            case 'confirm_order':
                $stmt = $pdo->prepare("UPDATE orders SET status = 'paid', waktu_bayar = NOW() WHERE id = ?");
                $stmt->execute([$order_id]);
                
                // Send notification if enabled
                if (isset($_SESSION['notification_settings']['email_notifications']) && $_SESSION['notification_settings']['email_notifications']) {
                    require_once 'notification_system.php';
                    sendOrderConfirmationNotification($order_id);
                }
                break;
            case 'reject_order':
                $stmt = $pdo->prepare("UPDATE orders SET status = 'failed' WHERE id = ?");
                $stmt->execute([$order_id]);
                
                // Send notification if enabled
                if (isset($_SESSION['notification_settings']['email_notifications']) && $_SESSION['notification_settings']['email_notifications']) {
                    require_once 'notification_system.php';
                    sendOrderRejectionNotification($order_id);
                }
                break;
            case 'delete_order':
                $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                $stmt->execute([$order_id]);
                break;
        }
        header('Location: admin_tiket_management.php');
        exit();
    }
}

// Get all orders with user information
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.email as user_email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.waktu_pesan DESC
");
$stmt->execute();
$orders = $stmt->fetchAll();

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders");
$stmt->execute();
$total_orders = $stmt->fetch()['total_orders'];

$stmt = $pdo->prepare("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'");
$stmt->execute();
$pending_orders = $stmt->fetch()['pending_orders'];

$stmt = $pdo->prepare("SELECT COUNT(*) as paid_orders FROM orders WHERE status = 'paid'");
$stmt->execute();
$paid_orders = $stmt->fetch()['paid_orders'];

$stmt = $pdo->prepare("SELECT COUNT(*) as failed_orders FROM orders WHERE status = 'failed'");
$stmt->execute();
$failed_orders = $stmt->fetch()['failed_orders'];

// Calculate total revenue
$stmt = $pdo->prepare("
    SELECT SUM(
        CASE 
            WHEN kategori = 'dewasa' THEN jumlah * 50000
            WHEN kategori = 'anak' THEN jumlah * 30000
            WHEN kategori = 'keluarga' THEN jumlah * 120000
        END
    ) as total_revenue 
    FROM orders 
    WHERE status = 'paid'
");
$stmt->execute();
$total_revenue = $stmt->fetch()['total_revenue'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tiket - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/admin-tailwind.css">
    <link rel="stylesheet" href="../assets/css/admin-tiket-management.css">
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
                            <a href="#" class="text-indigo-600 px-3 py-2 rounded-md text-sm font-medium border-b-2 border-indigo-600">Manajemen Tiket</a>
                            <a href="admin_settings.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Pengaturan</a>
                            <a href="../pages/dashboard.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Zoo Dashboard</a>
                            <a href="../pages/tiket.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Tiket</a>
                            <a href="../pages/statistik.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Statistik</a>
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
                <i class="fas fa-ticket-alt text-green-500 mr-3"></i>Manajemen Tiket
            </h1>
            <p class="text-gray-600 text-lg">Kelola dan konfirmasi pesanan tiket dari pengguna</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-ticket-alt text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Pesanan</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $total_orders; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-clock text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Menunggu Konfirmasi</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $pending_orders; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Dikonfirmasi</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $paid_orders; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-red-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-times-circle text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Ditolak</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $failed_orders; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-money-bill-wave text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Pendapatan</p>
                        <p class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Management Section -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-list-alt text-indigo-600 mr-3"></i>
                    Daftar Pesanan Tiket
                </h2>
                <p class="text-gray-600 mt-1">Kelola semua pesanan tiket dari pengguna</p>
            </div>

            <!-- Search and Filter -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" id="searchOrder" placeholder="Cari pesanan..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent search-input">
                    </div>
                    <div class="flex gap-2">
                        <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Semua Status</option>
                            <option value="pending">Menunggu Konfirmasi</option>
                            <option value="paid">Dikonfirmasi</option>
                            <option value="failed">Ditolak</option>
                        </select>
                        <select id="categoryFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Semua Kategori</option>
                            <option value="dewasa">Dewasa</option>
                            <option value="anak">Anak-anak</option>
                            <option value="keluarga">Keluarga</option>
                        </select>
                    </div>
                </div>
                
                <!-- Bulk Actions -->
                <div class="mt-4 flex flex-col md:flex-row gap-4 items-center">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="selectAll" class="text-sm font-medium text-gray-700">Pilih Semua</label>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        <button id="bulkConfirm" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <i class="fas fa-check mr-2"></i>Konfirmasi
                        </button>
                        <button id="bulkReject" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <i class="fas fa-times mr-2"></i>Tolak
                        </button>
                        <button id="bulkDelete" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <i class="fas fa-trash mr-2"></i>Hapus
                        </button>
                        <button id="bulkExport" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <i class="fas fa-download mr-2"></i>Export
                        </button>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAllHeader" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemesan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detail Tiket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pesan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($orders as $order): ?>
                        <?php 
                        // Calculate price based on category
                        $price_per_ticket = 0;
                        switch($order['kategori']) {
                            case 'dewasa':
                                $price_per_ticket = 50000;
                                break;
                            case 'anak':
                                $price_per_ticket = 30000;
                                break;
                            case 'keluarga':
                                $price_per_ticket = 120000;
                                break;
                        }
                        $total_price = $price_per_ticket * $order['jumlah'];
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200 order-row" 
                            data-status="<?php echo htmlspecialchars($order['status']); ?>" 
                            data-category="<?php echo htmlspecialchars($order['kategori']); ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="order-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" value="<?php echo $order['id']; ?>">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">#<?php echo $order['id']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <i class="fas fa-user text-indigo-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($order['nama']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($order['email']); ?></div>
                                        <div class="text-xs text-gray-400">User: <?php echo htmlspecialchars($order['username']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="font-medium"><?php echo ucfirst($order['kategori']); ?></div>
                                    <div class="text-gray-500">Jumlah: <?php echo $order['jumlah']; ?> tiket</div>
                                    <div class="text-gray-500">Tanggal: <?php echo date('d M Y', strtotime($order['tanggal'])); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    <?php 
                                    switch($order['status']) {
                                        case 'pending':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'paid':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'failed':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                    }
                                    ?>">
                                    <?php 
                                    switch($order['status']) {
                                        case 'pending':
                                            echo 'Menunggu Konfirmasi';
                                            break;
                                        case 'paid':
                                            echo 'Dikonfirmasi';
                                            break;
                                        case 'failed':
                                            echo 'Ditolak';
                                            break;
                                    }
                                    ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">Rp <?php echo number_format($total_price, 0, ',', '.'); ?></div>
                                <div class="text-xs text-gray-500">@Rp <?php echo number_format($price_per_ticket, 0, ',', '.'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d M Y H:i', strtotime($order['waktu_pesan'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <?php if ($order['status'] === 'pending'): ?>
                                    <button onclick="confirmOrder(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['nama']); ?>')" 
                                            class="text-green-600 hover:text-green-900 transition-colors duration-200" 
                                            title="Konfirmasi Pesanan">
                                        <i class="fas fa-check-circle text-lg"></i>
                                    </button>
                                    <button onclick="rejectOrder(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['nama']); ?>')" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-200" 
                                            title="Tolak Pesanan">
                                        <i class="fas fa-times-circle text-lg"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-200" 
                                            title="Lihat Detail">
                                        <i class="fas fa-eye text-lg"></i>
                                    </button>
                                    <button onclick="deleteOrder(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['nama']); ?>')" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-200" 
                                            title="Hapus Pesanan">
                                        <i class="fas fa-trash text-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-download text-xl"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-800">Export Data</h3>
                </div>
                <p class="text-gray-600 mb-4">Export data pesanan dalam format CSV atau Excel</p>
                <button onclick="exportOrderData()" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition duration-300">
                    <i class="fas fa-download mr-2"></i>Export Orders
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-chart-bar text-xl"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-800">Laporan</h3>
                </div>
                <p class="text-gray-600 mb-4">Lihat laporan penjualan dan statistik tiket</p>
                <button onclick="showReport()" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition duration-300">
                    <i class="fas fa-chart-bar mr-2"></i>View Report
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-cog text-xl"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-800">Pengaturan</h3>
                </div>
                <p class="text-gray-600 mb-4">Atur harga tiket dan pengaturan sistem</p>
                <button onclick="showSettings()" class="w-full bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded-lg transition duration-300">
                    <i class="fas fa-cog mr-2"></i>Settings
                </button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-4xl text-yellow-500 mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Konfirmasi</h3>
                    <p id="confirmMessage" class="text-gray-600 mb-6"></p>
                    <div class="flex space-x-3">
                        <button id="confirmYes" class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg transition duration-300">
                            Ya
                        </button>
                        <button onclick="closeConfirmModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg transition duration-300">
                            Tidak
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Detail Pesanan</h3>
                    <button onclick="closeOrderDetailsModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="orderDetailsContent" class="space-y-4">
                    <!-- Order details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Search and Filter functionality
        document.getElementById('searchOrder').addEventListener('input', filterOrders);
        document.getElementById('statusFilter').addEventListener('change', filterOrders);
        document.getElementById('categoryFilter').addEventListener('change', filterOrders);

        function filterOrders() {
            const searchTerm = document.getElementById('searchOrder').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const categoryFilter = document.getElementById('categoryFilter').value;
            const rows = document.querySelectorAll('.order-row');

            rows.forEach(row => {
                const orderText = row.textContent.toLowerCase();
                const status = row.dataset.status;
                const category = row.dataset.category;

                const matchesSearch = orderText.includes(searchTerm);
                const matchesStatus = !statusFilter || status === statusFilter;
                const matchesCategory = !categoryFilter || category === categoryFilter;

                if (matchesSearch && matchesStatus && matchesCategory) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Modal functions
        function showConfirmModal(message, action) {
            document.getElementById('confirmMessage').textContent = message;
            document.getElementById('confirmYes').onclick = action;
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.add('hidden');
        }

        function closeOrderDetailsModal() {
            document.getElementById('orderDetailsModal').classList.add('hidden');
        }

        // Order management functions
        function confirmOrder(orderId, customerName) {
            showConfirmModal(
                `Apakah Anda yakin ingin mengkonfirmasi pesanan dari "${customerName}"?`,
                () => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="confirm_order">
                        <input type="hidden" name="order_id" value="${orderId}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            );
        }

        function rejectOrder(orderId, customerName) {
            showConfirmModal(
                `Apakah Anda yakin ingin menolak pesanan dari "${customerName}"?`,
                () => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="reject_order">
                        <input type="hidden" name="order_id" value="${orderId}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            );
        }

        function deleteOrder(orderId, customerName) {
            showConfirmModal(
                `Apakah Anda yakin ingin menghapus pesanan dari "${customerName}"? Tindakan ini tidak dapat dibatalkan.`,
                () => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="delete_order">
                        <input type="hidden" name="order_id" value="${orderId}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            );
        }

        function viewOrderDetails(orderId) {
            // Load order details via AJAX
            fetch(`get_order_details.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('orderDetailsContent').innerHTML = data.html;
                        document.getElementById('orderDetailsModal').classList.remove('hidden');
                    } else {
                        alert('Gagal memuat detail pesanan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat detail pesanan');
                });
        }

        function exportOrderData() {
            // Get current filters
            const statusFilter = document.getElementById('statusFilter').value;
            const categoryFilter = document.getElementById('categoryFilter').value;
            const dateFromFilter = document.getElementById('dateFromFilter')?.value || '';
            const dateToFilter = document.getElementById('dateToFilter')?.value || '';
            
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Exporting...';
            button.disabled = true;
            
            // Prepare form data
            const formData = new FormData();
            formData.append('action', 'export_filtered');
            if (statusFilter) formData.append('status', statusFilter);
            if (categoryFilter) formData.append('category', categoryFilter);
            if (dateFromFilter) formData.append('date_from', dateFromFilter);
            if (dateToFilter) formData.append('date_to', dateToFilter);
            
            // Send export request
            fetch('tiket_export.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification(data.message, 'success');
                    
                    // Auto download the file
                    setTimeout(() => {
                        window.open(data.download_url, '_blank');
                    }, 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error exporting data: ' + error.message, 'error');
            })
            .finally(() => {
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function showReport() {
            // Show report modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 z-50';
            modal.innerHTML = `
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Laporan Penjualan Tiket</h3>
                            <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-medium text-blue-600">Total Pendapatan</h4>
                                    <p class="text-2xl font-bold text-blue-800">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></p>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-medium text-green-600">Tiket Terjual</h4>
                                    <p class="text-2xl font-bold text-green-800"><?php echo $paid_orders; ?></p>
                                </div>
                                <div class="bg-yellow-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-medium text-yellow-600">Menunggu Konfirmasi</h4>
                                    <p class="text-2xl font-bold text-yellow-800"><?php echo $pending_orders; ?></p>
                                </div>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button onclick="exportReport()" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition duration-300">
                                    <i class="fas fa-download mr-2"></i>Export Laporan
                                </button>
                                <button onclick="this.closest('.fixed').remove()" class="bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg transition duration-300">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        function exportReport() {
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Exporting...';
            button.disabled = true;
            
            // Export all tickets
            const formData = new FormData();
            formData.append('action', 'export_all');
            
            fetch('tiket_export.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification(data.message, 'success');
                    
                    // Auto download the file
                    setTimeout(() => {
                        window.open(data.download_url, '_blank');
                    }, 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error exporting report: ' + error.message, 'error');
            })
            .finally(() => {
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function showSettings() {
            // Redirect to settings page
            window.location.href = 'admin_settings.php';
        }

        // Bulk actions functionality
        const selectAllCheckbox = document.getElementById('selectAll');
        const selectAllHeader = document.getElementById('selectAllHeader');
        const orderCheckboxes = document.querySelectorAll('.order-checkbox');
        const bulkButtons = document.querySelectorAll('#bulkConfirm, #bulkReject, #bulkDelete, #bulkExport');

        function updateBulkButtons() {
            const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
            const hasChecked = checkedBoxes.length > 0;
            
            bulkButtons.forEach(button => {
                button.disabled = !hasChecked;
            });
        }

        function getSelectedOrderIds() {
            const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
            return Array.from(checkedBoxes).map(cb => cb.value);
        }

        // Select all functionality
        selectAllCheckbox.addEventListener('change', function() {
            orderCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            selectAllHeader.checked = this.checked;
            updateBulkButtons();
        });

        selectAllHeader.addEventListener('change', function() {
            orderCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            selectAllCheckbox.checked = this.checked;
            updateBulkButtons();
        });

        // Individual checkbox change
        orderCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(orderCheckboxes).every(cb => cb.checked);
                const anyChecked = Array.from(orderCheckboxes).some(cb => cb.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllHeader.checked = allChecked;
                updateBulkButtons();
            });
        });

        // Bulk action handlers
        document.getElementById('bulkConfirm').addEventListener('click', function() {
            const orderIds = getSelectedOrderIds();
            if (orderIds.length === 0) return;
            
            showConfirmModal(
                `Apakah Anda yakin ingin mengkonfirmasi ${orderIds.length} pesanan?`,
                () => performBulkAction('bulk_confirm', orderIds)
            );
        });

        document.getElementById('bulkReject').addEventListener('click', function() {
            const orderIds = getSelectedOrderIds();
            if (orderIds.length === 0) return;
            
            showConfirmModal(
                `Apakah Anda yakin ingin menolak ${orderIds.length} pesanan?`,
                () => performBulkAction('bulk_reject', orderIds)
            );
        });

        document.getElementById('bulkDelete').addEventListener('click', function() {
            const orderIds = getSelectedOrderIds();
            if (orderIds.length === 0) return;
            
            showConfirmModal(
                `Apakah Anda yakin ingin menghapus ${orderIds.length} pesanan? Tindakan ini tidak dapat dibatalkan.`,
                () => performBulkAction('bulk_delete', orderIds)
            );
        });

        document.getElementById('bulkExport').addEventListener('click', function() {
            const orderIds = getSelectedOrderIds();
            if (orderIds.length === 0) return;
            
            performBulkAction('bulk_export', orderIds);
        });

        function performBulkAction(action, orderIds) {
            const formData = new FormData();
            formData.append('action', action);
            orderIds.forEach(id => formData.append('order_ids[]', id));
            
            if (action === 'bulk_export') {
                // Create a temporary form for download
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin_tiket_bulk_actions.php';
                formData.forEach((value, key) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
                return;
            }
            
            fetch('admin_tiket_bulk_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Auto download if download_url is provided
                    if (data.download_url) {
                        setTimeout(() => {
                            window.open(data.download_url, '_blank');
                        }, 1000);
                    }
                    // Uncheck all checkboxes
                    orderCheckboxes.forEach(cb => cb.checked = false);
                    selectAllCheckbox.checked = false;
                    selectAllHeader.checked = false;
                    updateBulkButtons();
                    // Reload page after a short delay
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Terjadi kesalahan: ' + error.message, 'error');
            });
        }

        // Notification function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Close modals when clicking outside
        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeConfirmModal();
            }
        });

        document.getElementById('orderDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderDetailsModal();
            }
        });

        // Initialize bulk buttons state
        updateBulkButtons();
    </script>
</body>
</html> 