<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $order_ids = $_POST['order_ids'] ?? [];
    
    if (empty($order_ids)) {
        echo json_encode(['success' => false, 'message' => 'No orders selected']);
        exit();
    }
    
    // Convert string to array if needed
    if (is_string($order_ids)) {
        $order_ids = explode(',', $order_ids);
    }
    
    // Sanitize order IDs
    $order_ids = array_map('intval', $order_ids);
    $order_ids = array_filter($order_ids);
    
    if (empty($order_ids)) {
        echo json_encode(['success' => false, 'message' => 'Invalid order IDs']);
        exit();
    }
    
    $placeholders = str_repeat('?,', count($order_ids) - 1) . '?';
    
    switch ($action) {
        case 'bulk_confirm':
            bulkConfirmOrders($order_ids, $placeholders);
            break;
        case 'bulk_reject':
            bulkRejectOrders($order_ids, $placeholders);
            break;
        case 'bulk_delete':
            bulkDeleteOrders($order_ids, $placeholders);
            break;
        case 'bulk_export':
            bulkExportOrders($order_ids, $placeholders);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit();
    }
}

function bulkConfirmOrders($order_ids, $placeholders) {
    global $pdo;
    
    try {
        // Update orders status to paid
        $stmt = $pdo->prepare("UPDATE orders SET status = 'paid', waktu_bayar = NOW() WHERE id IN ($placeholders) AND status = 'pending'");
        $stmt->execute($order_ids);
        
        $affected_rows = $stmt->rowCount();
        
        // Log the action
        logBulkAction('bulk_confirm', $order_ids, $affected_rows);
        
        echo json_encode([
            'success' => true, 
            'message' => "Successfully confirmed $affected_rows orders",
            'affected_rows' => $affected_rows
        ]);
    } catch (Exception $e) {
        error_log("Bulk confirm error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error confirming orders']);
    }
}

function bulkRejectOrders($order_ids, $placeholders) {
    global $pdo;
    
    try {
        // Update orders status to failed
        $stmt = $pdo->prepare("UPDATE orders SET status = 'failed' WHERE id IN ($placeholders) AND status = 'pending'");
        $stmt->execute($order_ids);
        
        $affected_rows = $stmt->rowCount();
        
        // Log the action
        logBulkAction('bulk_reject', $order_ids, $affected_rows);
        
        echo json_encode([
            'success' => true, 
            'message' => "Successfully rejected $affected_rows orders",
            'affected_rows' => $affected_rows
        ]);
    } catch (Exception $e) {
        error_log("Bulk reject error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error rejecting orders']);
    }
}

function bulkDeleteOrders($order_ids, $placeholders) {
    global $pdo;
    
    try {
        // Get order details before deletion for logging
        $stmt = $pdo->prepare("SELECT id, user_id, nama, email, kategori, jumlah FROM orders WHERE id IN ($placeholders)");
        $stmt->execute($order_ids);
        $orders_to_delete = $stmt->fetchAll();
        
        // Delete orders
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id IN ($placeholders)");
        $stmt->execute($order_ids);
        
        $affected_rows = $stmt->rowCount();
        
        // Log the action with details
        logBulkAction('bulk_delete', $order_ids, $affected_rows, $orders_to_delete);
        
        echo json_encode([
            'success' => true, 
            'message' => "Successfully deleted $affected_rows orders",
            'affected_rows' => $affected_rows
        ]);
    } catch (Exception $e) {
        error_log("Bulk delete error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error deleting orders']);
    }
}

function bulkExportOrders($order_ids, $placeholders) {
    global $pdo;
    
    try {
        // Get orders with user information
        $stmt = $pdo->prepare("
            SELECT o.*, u.username, u.email as user_email 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id IN ($placeholders)
            ORDER BY o.waktu_pesan DESC
        ");
        $stmt->execute($order_ids);
        $orders = $stmt->fetchAll();
        
        if (empty($orders)) {
            echo json_encode(['success' => false, 'message' => 'No orders found to export']);
            return;
        }
        
        // Generate CSV content
        $csv_content = generateCSVContent($orders);
        
        // Create filename with timestamp
        $filename = 'orders_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Save to file
        $filepath = 'exports/' . $filename;
        if (!is_dir('exports')) {
            mkdir('exports', 0755, true);
        }
        
        file_put_contents($filepath, $csv_content);
        
        // Log the action
        logBulkAction('bulk_export', $order_ids, count($orders), null, $filename);
        
        echo json_encode([
            'success' => true, 
            'message' => "Successfully exported " . count($orders) . " orders",
            'download_url' => $filepath,
            'filename' => $filename,
            'count' => count($orders)
        ]);
    } catch (Exception $e) {
        error_log("Bulk export error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error exporting orders']);
    }
}

function generateCSVContent($orders) {
    $csv_content = "ID,Username,Email,Nama,Tanggal,Jumlah,Kategori,Status,Metode Pembayaran,Waktu Pesan,Waktu Bayar\n";
    
    foreach ($orders as $order) {
        $csv_content .= sprintf(
            "%d,%s,%s,%s,%s,%d,%s,%s,%s,%s,%s\n",
            $order['id'],
            escapeCSV($order['username']),
            escapeCSV($order['user_email']),
            escapeCSV($order['nama']),
            $order['tanggal'],
            $order['jumlah'],
            $order['kategori'],
            $order['status'],
            escapeCSV($order['metode_pembayaran'] ?? ''),
            $order['waktu_pesan'],
            $order['waktu_bayar'] ?? ''
        );
    }
    
    return $csv_content;
}

function escapeCSV($value) {
    if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
        return '"' . str_replace('"', '""', $value) . '"';
    }
    return $value;
}

function logBulkAction($action, $order_ids, $affected_rows, $details = null, $filename = null) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'admin_id' => $_SESSION['user_id'],
        'admin_username' => $_SESSION['username'] ?? 'unknown',
        'order_ids' => $order_ids,
        'affected_rows' => $affected_rows,
        'details' => $details,
        'filename' => $filename
    ];
    
    $log_file = 'logs/bulk_actions.log';
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
}

// Handle file download requests
if (isset($_GET['download']) && $_GET['download'] === 'true') {
    $filename = $_GET['filename'] ?? '';
    
    if (empty($filename) || !file_exists('exports/' . $filename)) {
        http_response_code(404);
        echo 'File not found';
        exit();
    }
    
    // Security check - only allow CSV files
    if (pathinfo($filename, PATHINFO_EXTENSION) !== 'csv') {
        http_response_code(403);
        echo 'Invalid file type';
        exit();
    }
    
    $filepath = 'exports/' . $filename;
    
    // Set headers for download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    
    // Output file content
    readfile($filepath);
    exit();
}

// If not AJAX request, show the UI
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Actions - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/admin-tiket-bulk-actions.css">
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% {
                transform: translate3d(0,0,0);
            }
            40%, 43% {
                transform: translate3d(0, -30px, 0);
            }
            70% {
                transform: translate3d(0, -15px, 0);
            }
            90% {
                transform: translate3d(0, -4px, 0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .animate-slide-in-right {
            animation: slideInRight 0.6s ease-out;
        }
        
        .animate-pulse-slow {
            animation: pulse 2s infinite;
        }
        
        .animate-bounce-slow {
            animation: bounce 2s infinite;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .hover-lift {
            transition: all 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-100 min-h-screen">
    <!-- Navigation -->
    <nav class="glass-effect-enhanced shadow-lg border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-alt text-3xl text-indigo-600 animate-pulse-slow"></i>
                    </div>
                    <div class="hidden md:block ml-4">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="admin_dashboard.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">Dashboard</a>
                            <a href="admin_analytics.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">Analytics</a>
                            <a href="admin_tiket_management.php" class="text-indigo-600 px-3 py-2 rounded-md text-sm font-medium border-b-2 border-indigo-600">Manajemen Tiket</a>
                            <a href="admin_settings.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">Pengaturan</a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700 text-sm">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                        <a href="../pages/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover-lift">
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
        <div class="mb-8 animate-fade-in-up">
            <h1 class="text-4xl font-bold gradient-text mb-2">
                <i class="fas fa-tasks text-indigo-600 mr-3 animate-bounce-slow"></i>Bulk Actions
            </h1>
            <p class="text-gray-600 text-lg">Kelola pesanan tiket secara massal dengan mudah</p>
        </div>

        <!-- Bulk Actions Panel -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 animate-slide-in-right card-hover">
            <div class="flex items-center mb-6">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600 mr-4 animate-float">
                    <i class="fas fa-magic text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Bulk Operations</h2>
                    <p class="text-gray-600">Pilih pesanan dan lakukan aksi massal</p>
                </div>
            </div>

            <!-- Selection Controls -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <button id="selectAll" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced">
                    <i class="fas fa-check-double mr-2"></i>Select All
                </button>
                <button id="deselectAll" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced">
                    <i class="fas fa-times mr-2"></i>Deselect All
                </button>
                <button id="selectPending" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced">
                    <i class="fas fa-clock mr-2"></i>Select Pending
                </button>
                <button id="selectPaid" class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced">
                    <i class="fas fa-check mr-2"></i>Select Paid
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <button id="bulkConfirm" class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-check-circle mr-2"></i>Confirm Orders
                </button>
                <button id="bulkReject" class="bg-red-500 hover:bg-red-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-times-circle mr-2"></i>Reject Orders
                </button>
                <button id="bulkDelete" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-trash mr-2"></i>Delete Orders
                </button>
                <button id="bulkExport" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 btn-enhanced disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-download mr-2"></i>Export Orders
                </button>
            </div>

            <!-- Status Display -->
            <div id="statusDisplay" class="hidden">
                <div class="flex items-center p-4 rounded-lg bg-blue-50 border border-blue-200">
                    <div class="spinner-enhanced mr-3"></div>
                    <div class="flex-1">
                        <span class="text-blue-800 font-medium">Processing...</span>
                        <div class="progress-bar mt-2"></div>
                    </div>
                </div>
            </div>

            <!-- Results Display -->
            <div id="resultsDisplay" class="hidden">
                <div class="p-4 rounded-lg bg-green-50 border border-green-200">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 text-xl mr-3"></i>
                        <span id="resultMessage" class="text-green-800 font-medium"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in-up card-hover" style="animation-delay: 0.2s;">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800">Orders List</h3>
                <p class="text-gray-600 text-sm">Select orders to perform bulk actions</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAllCheckbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody" class="bg-white divide-y divide-gray-200">
                        <!-- Orders will be loaded here via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Download Modal -->
    <div id="downloadModal" class="fixed inset-0 modal-backdrop hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full modal-enhanced">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <i class="fas fa-download text-green-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Export Successful!</h3>
                    <p class="text-sm text-gray-500 mb-6">Your file has been generated successfully.</p>
                    <div class="flex space-x-3">
                        <button id="downloadFile" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            <i class="fas fa-download mr-2"></i>Download
                        </button>
                        <button id="closeModal" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let selectedOrders = [];
        let allOrders = [];

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            loadOrders();
            setupEventListeners();
        });

        function loadOrders() {
            // Simulate loading orders (replace with actual AJAX call)
            fetch('../admin/admin_tiket_management.php?ajax=1')
                .then(response => response.json())
                .then(data => {
                    allOrders = data.orders || [];
                    renderOrdersTable();
                })
                .catch(error => {
                    console.error('Error loading orders:', error);
                    // Fallback to sample data
                    allOrders = [
                        {id: 1, username: 'john_doe', nama: 'John Doe', kategori: 'dewasa', jumlah: 2, status: 'pending', tanggal: '2024-01-15'},
                        {id: 2, username: 'jane_smith', nama: 'Jane Smith', kategori: 'anak', jumlah: 1, status: 'paid', tanggal: '2024-01-14'},
                        {id: 3, username: 'bob_wilson', nama: 'Bob Wilson', kategori: 'keluarga', jumlah: 1, status: 'pending', tanggal: '2024-01-13'}
                    ];
                    renderOrdersTable();
                });
        }

        function renderOrdersTable() {
            const tbody = document.getElementById('ordersTableBody');
            tbody.innerHTML = '';

            allOrders.forEach((order, index) => {
                const row = document.createElement('tr');
                row.className = `hover:bg-gray-50 transition-colors duration-200 table-row-animate`;
                row.style.animationDelay = `${index * 0.1}s`;
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" class="order-checkbox enhanced-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" value="${order.id}">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#${order.id}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${order.nama}</div>
                        <div class="text-sm text-gray-500">@${order.username}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${order.kategori} - ${order.jumlah} tiket</div>
                        <div class="text-sm text-gray-500">${getCategoryPrice(order.kategori)}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full status-badge ${getStatusClass(order.status)}">
                            ${order.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.tanggal}</td>
                `;
                tbody.appendChild(row);
            });

            // Add event listeners to checkboxes
            document.querySelectorAll('.order-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedOrders);
            });
        }

        function getCategoryPrice(kategori) {
            const prices = {
                'dewasa': 'Rp 50.000',
                'anak': 'Rp 30.000',
                'keluarga': 'Rp 120.000'
            };
            return prices[kategori] || 'Rp 0';
        }

        function getStatusClass(status) {
            const classes = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'paid': 'bg-green-100 text-green-800',
                'failed': 'bg-red-100 text-red-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        }

        function setupEventListeners() {
            // Select all checkbox
            document.getElementById('selectAllCheckbox').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.order-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectedOrders();
            });

            // Selection buttons
            document.getElementById('selectAll').addEventListener('click', selectAll);
            document.getElementById('deselectAll').addEventListener('click', deselectAll);
            document.getElementById('selectPending').addEventListener('click', () => selectByStatus('pending'));
            document.getElementById('selectPaid').addEventListener('click', () => selectByStatus('paid'));

            // Action buttons
            document.getElementById('bulkConfirm').addEventListener('click', () => performBulkAction('bulk_confirm'));
            document.getElementById('bulkReject').addEventListener('click', () => performBulkAction('bulk_reject'));
            document.getElementById('bulkDelete').addEventListener('click', () => performBulkAction('bulk_delete'));
            document.getElementById('bulkExport').addEventListener('click', () => performBulkAction('bulk_export'));

            // Modal buttons
            document.getElementById('downloadFile').addEventListener('click', downloadFile);
            document.getElementById('closeModal').addEventListener('click', closeModal);
        }

        function updateSelectedOrders() {
            selectedOrders = Array.from(document.querySelectorAll('.order-checkbox:checked'))
                .map(checkbox => parseInt(checkbox.value));
            
            updateActionButtons();
        }

        function updateActionButtons() {
            const buttons = ['bulkConfirm', 'bulkReject', 'bulkDelete', 'bulkExport'];
            const hasSelection = selectedOrders.length > 0;
            
            buttons.forEach(buttonId => {
                const button = document.getElementById(buttonId);
                button.disabled = !hasSelection;
                if (hasSelection) {
                    button.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    button.classList.add('opacity-50', 'cursor-not-allowed');
                }
            });
        }

        function selectAll() {
            document.querySelectorAll('.order-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
            document.getElementById('selectAllCheckbox').checked = true;
            updateSelectedOrders();
        }

        function deselectAll() {
            document.querySelectorAll('.order-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('selectAllCheckbox').checked = false;
            updateSelectedOrders();
        }

        function selectByStatus(status) {
            document.querySelectorAll('.order-checkbox').forEach((checkbox, index) => {
                if (allOrders[index] && allOrders[index].status === status) {
                    checkbox.checked = true;
                }
            });
            updateSelectedOrders();
        }

        function performBulkAction(action) {
            if (selectedOrders.length === 0) {
                showNotification('Please select at least one order', 'warning');
                return;
            }

            // Show confirmation for destructive actions
            if (action === 'bulk_delete') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: `This will permanently delete ${selectedOrders.length} order(s). This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete them!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        executeBulkAction(action);
                    }
                });
            } else {
                executeBulkAction(action);
            }
        }

        function executeBulkAction(action) {
            showStatus('Processing...');
            
            const formData = new FormData();
            formData.append('action', action);
            formData.append('order_ids', selectedOrders.join(','));

            fetch('admin_tiket_bulk_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideStatus();
                if (data.success) {
                    showNotification(data.message, 'success');
                    if (action === 'bulk_export' && data.download_url) {
                        showDownloadModal(data.filename);
                    } else {
                        // Refresh the orders list
                        loadOrders();
                    }
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                hideStatus();
                console.error('Error:', error);
                showNotification('An error occurred while processing the request', 'error');
            });
        }

        function showStatus(message) {
            document.getElementById('statusDisplay').classList.remove('hidden');
        }

        function hideStatus() {
            document.getElementById('statusDisplay').classList.add('hidden');
        }

        function showNotification(message, type) {
            const icon = type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'error';
            const color = type === 'success' ? '#10b981' : type === 'warning' ? '#f59e0b' : '#ef4444';
            
            Swal.fire({
                title: type === 'success' ? 'Success!' : type === 'warning' ? 'Warning!' : 'Error!',
                text: message,
                icon: icon,
                confirmButtonColor: color,
                timer: type === 'success' ? 3000 : undefined,
                timerProgressBar: type === 'success'
            });
        }

        function showDownloadModal(filename) {
            document.getElementById('downloadModal').classList.remove('hidden');
            // Store filename for download
            document.getElementById('downloadFile').dataset.filename = filename;
        }

        function closeModal() {
            document.getElementById('downloadModal').classList.add('hidden');
        }

        function downloadFile() {
            const filename = document.getElementById('downloadFile').dataset.filename;
            if (filename) {
                window.open(`admin_tiket_bulk_actions.php?download=true&filename=${filename}`, '_blank');
                closeModal();
            }
        }

        // Close modal when clicking outside
        document.getElementById('downloadModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
<?php
}
?> 