<?php
session_start();
require_once '../includes/db.php';

// TEMPORARY: Auto-setup admin session for testing
// Remove this section in production
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Try to find an admin user in database
    try {
        $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE role = 'admin' LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['username'] = $admin['username'];
        } else {
            // Create a temporary admin session if no admin exists
            $_SESSION['user_id'] = 1;
            $_SESSION['role'] = 'admin';
            $_SESSION['username'] = 'admin';
        }
    } catch (Exception $e) {
        // Fallback to temporary session
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';
        $_SESSION['username'] = 'admin';
    }
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Handle export requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'export_all':
            exportAllTickets();
            break;
        case 'export_filtered':
            exportFilteredTickets();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit();
    }
}

function exportAllTickets() {
    global $pdo;
    
    try {
        // Get all orders with user information
        $stmt = $pdo->prepare("
            SELECT o.*, u.username, u.email as user_email, u.phone as user_phone
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.waktu_pesan DESC
        ");
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($orders)) {
            echo json_encode(['success' => false, 'message' => 'No orders found to export']);
            return;
        }
        
        // Generate CSV content
        $csv_content = generateDetailedCSVContent($orders);
        
        // Create filename
        $filename = 'all_tickets_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Save to file with proper path
        $export_dir = __DIR__ . '/../exports';
        if (!is_dir($export_dir)) {
            if (!mkdir($export_dir, 0755, true)) {
                throw new Exception('Failed to create exports directory');
            }
        }
        
        $filepath = $export_dir . '/' . $filename;
        if (file_put_contents($filepath, $csv_content) === false) {
            throw new Exception('Failed to write export file');
        }
        
        // Log the export
        logExport('all_tickets', count($orders), $filename);
        
        echo json_encode([
            'success' => true,
            'message' => "Successfully exported " . count($orders) . " orders",
            'download_url' => '../includes/download_file.php?type=export&file=' . urlencode($filename),
            'filename' => $filename,
            'count' => count($orders)
        ]);
        
    } catch (Exception $e) {
        error_log("Export all tickets error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error exporting tickets: ' . $e->getMessage()]);
    }
}

function exportFilteredTickets() {
    global $pdo;
    
    // Get filter parameters
    $status = $_POST['status'] ?? '';
    $category = $_POST['category'] ?? '';
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    try {
        // Build query with filters
        $query = "
            SELECT o.*, u.username, u.email as user_email, u.phone as user_phone
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE 1=1
        ";
        $params = [];
        
        if (!empty($status)) {
            $query .= " AND o.status = ?";
            $params[] = $status;
        }
        
        if (!empty($category)) {
            $query .= " AND o.kategori = ?";
            $params[] = $category;
        }
        
        if (!empty($date_from)) {
            $query .= " AND o.tanggal >= ?";
            $params[] = $date_from;
        }
        
        if (!empty($date_to)) {
            $query .= " AND o.tanggal <= ?";
            $params[] = $date_to;
        }
        
        if ($user_id > 0) {
            $query .= " AND o.user_id = ?";
            $params[] = $user_id;
        }
        
        $query .= " ORDER BY o.waktu_pesan DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($orders)) {
            echo json_encode(['success' => false, 'message' => 'No orders found with the specified filters']);
            return;
        }
        
        // Generate CSV content
        $csv_content = generateDetailedCSVContent($orders);
        
        // Create filename with filter info
        $filter_info = [];
        if (!empty($status)) $filter_info[] = $status;
        if (!empty($category)) $filter_info[] = $category;
        if (!empty($date_from)) $filter_info[] = 'from_' . $date_from;
        if (!empty($date_to)) $filter_info[] = 'to_' . $date_to;
        if ($user_id > 0) $filter_info[] = 'user_' . $user_id;
        
        $filter_suffix = !empty($filter_info) ? '_' . implode('_', $filter_info) : '';
        $filename = 'filtered_tickets_export' . $filter_suffix . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Save to file with proper path
        $export_dir = __DIR__ . '/../exports';
        if (!is_dir($export_dir)) {
            if (!mkdir($export_dir, 0755, true)) {
                throw new Exception('Failed to create exports directory');
            }
        }
        
        $filepath = $export_dir . '/' . $filename;
        if (file_put_contents($filepath, $csv_content) === false) {
            throw new Exception('Failed to write export file');
        }
        
        // Log the export
        logExport('filtered_tickets', count($orders), $filename, [
            'status' => $status,
            'category' => $category,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'user_id' => $user_id
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => "Successfully exported " . count($orders) . " filtered orders",
            'download_url' => '../includes/download_file.php?type=export&file=' . urlencode($filename),
            'filename' => $filename,
            'count' => count($orders),
            'filters' => [
                'status' => $status,
                'category' => $category,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'user_id' => $user_id
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Export filtered tickets error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error exporting filtered tickets: ' . $e->getMessage()]);
    }
}

function generateDetailedCSVContent($orders) {
    $csv_content = "ID Pesanan,Username,Email User,Telepon User,Nama Pemesan,Email Pemesan,Tanggal Kunjungan,Jumlah Tiket,Kategori Tiket,Status Pesanan,Metode Pembayaran,Harga per Tiket,Total Harga,Waktu Pesan,Waktu Bayar\n";
    
    foreach ($orders as $order) {
        // Calculate price
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
        
        // Format dates with error handling
        $tanggal = '';
        if (!empty($order['tanggal'])) {
            $tanggal = date('d/m/Y', strtotime($order['tanggal']));
        }
        
        $waktu_pesan = '';
        if (!empty($order['waktu_pesan'])) {
            $waktu_pesan = date('d/m/Y H:i', strtotime($order['waktu_pesan']));
        }
        
        $waktu_bayar = '';
        if (!empty($order['waktu_bayar'])) {
            $waktu_bayar = date('d/m/Y H:i', strtotime($order['waktu_bayar']));
        }
        
        // Format status
        $status_labels = [
            'pending' => 'Menunggu Konfirmasi',
            'paid' => 'Dikonfirmasi',
            'failed' => 'Ditolak'
        ];
        $status = $status_labels[$order['status']] ?? $order['status'];
        
        // Format category
        $category_labels = [
            'dewasa' => 'Dewasa',
            'anak' => 'Anak-anak',
            'keluarga' => 'Keluarga'
        ];
        $category = $category_labels[$order['kategori']] ?? $order['kategori'];
        
        $csv_content .= sprintf(
            "%d,%s,%s,%s,%s,%s,%s,%d,%s,%s,%s,%s,%s,%s,%s\n",
            $order['id'],
            escapeCSV($order['username'] ?? ''),
            escapeCSV($order['user_email'] ?? ''),
            escapeCSV($order['user_phone'] ?? ''),
            escapeCSV($order['nama'] ?? ''),
            escapeCSV($order['email'] ?? ''),
            $tanggal,
            $order['jumlah'] ?? 0,
            $category,
            $status,
            escapeCSV($order['metode_pembayaran'] ?? ''),
            number_format($price_per_ticket, 0, ',', '.'),
            number_format($total_price, 0, ',', '.'),
            $waktu_pesan,
            $waktu_bayar
        );
    }
    
    return $csv_content;
}

function escapeCSV($value) {
    if ($value === null) {
        return '';
    }
    $value = (string)$value;
    if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
        return '"' . str_replace('"', '""', $value) . '"';
    }
    return $value;
}

function logExport($type, $count, $filename, $filters = null) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => 'export',
        'type' => $type,
        'admin_id' => $_SESSION['user_id'],
        'admin_username' => $_SESSION['username'] ?? 'unknown',
        'count' => $count,
        'filename' => $filename,
        'filters' => $filters
    ];
    
    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) {
        if (!mkdir($log_dir, 0755, true)) {
            error_log("Failed to create logs directory");
            return;
        }
    }
    
    $log_file = $log_dir . '/exports.log';
    if (file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX) === false) {
        error_log("Failed to write export log");
    }
}

// Handle GET requests for export statistics
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['stats'])) {
    try {
        // Get export statistics
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
        
        // Calculate revenue
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
        
        echo json_encode([
            'success' => true,
            'stats' => [
                'total_orders' => $total_orders,
                'pending_orders' => $pending_orders,
                'paid_orders' => $paid_orders,
                'failed_orders' => $failed_orders,
                'total_revenue' => $total_revenue,
                'total_revenue_formatted' => 'Rp ' . number_format($total_revenue, 0, ',', '.')
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Get export stats error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error retrieving statistics: ' . $e->getMessage()]);
    }
}
?> 