<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    
    if ($order_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
        exit();
    }
    
    try {
        // Get order details with user information
        $stmt = $pdo->prepare("
            SELECT o.*, u.username, u.email as user_email, u.phone as user_phone
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit();
        }
        
        // Calculate price information
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
        
        // Format dates
        $order['tanggal_formatted'] = date('d F Y', strtotime($order['tanggal']));
        $order['waktu_pesan_formatted'] = date('d F Y H:i', strtotime($order['waktu_pesan']));
        $order['waktu_bayar_formatted'] = $order['waktu_bayar'] ? date('d F Y H:i', strtotime($order['waktu_bayar'])) : '-';
        
        // Add calculated fields
        $order['price_per_ticket'] = $price_per_ticket;
        $order['total_price'] = $total_price;
        $order['total_price_formatted'] = 'Rp ' . number_format($total_price, 0, ',', '.');
        
        // Get status information
        $status_info = getStatusInfo($order['status']);
        $order['status_info'] = $status_info;
        
        // Get category information
        $category_info = getCategoryInfo($order['kategori']);
        $order['category_info'] = $category_info;
        
        echo json_encode([
            'success' => true,
            'order' => $order
        ]);
        
    } catch (Exception $e) {
        error_log("Get order details error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error retrieving order details']);
    }
}

function getStatusInfo($status) {
    $statuses = [
        'pending' => [
            'label' => 'Menunggu Konfirmasi',
            'color' => 'yellow',
            'icon' => 'fas fa-clock',
            'description' => 'Pesanan sedang menunggu konfirmasi pembayaran'
        ],
        'paid' => [
            'label' => 'Dikonfirmasi',
            'color' => 'green',
            'icon' => 'fas fa-check-circle',
            'description' => 'Pesanan telah dikonfirmasi dan dibayar'
        ],
        'failed' => [
            'label' => 'Ditolak',
            'color' => 'red',
            'icon' => 'fas fa-times-circle',
            'description' => 'Pesanan ditolak atau pembayaran gagal'
        ]
    ];
    
    return $statuses[$status] ?? [
        'label' => 'Unknown',
        'color' => 'gray',
        'icon' => 'fas fa-question-circle',
        'description' => 'Status tidak diketahui'
    ];
}

function getCategoryInfo($category) {
    $categories = [
        'dewasa' => [
            'label' => 'Dewasa',
            'description' => 'Tiket untuk pengunjung dewasa (17+ tahun)',
            'icon' => 'fas fa-user',
            'color' => 'blue'
        ],
        'anak' => [
            'label' => 'Anak-anak',
            'description' => 'Tiket untuk pengunjung anak-anak (3-16 tahun)',
            'icon' => 'fas fa-child',
            'color' => 'green'
        ],
        'keluarga' => [
            'label' => 'Keluarga',
            'description' => 'Paket tiket keluarga (2 dewasa + 2 anak)',
            'icon' => 'fas fa-users',
            'color' => 'purple'
        ]
    ];
    
    return $categories[$category] ?? [
        'label' => 'Unknown',
        'description' => 'Kategori tidak diketahui',
        'icon' => 'fas fa-question-circle',
        'color' => 'gray'
    ];
}

// Handle GET requests for order history
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $user_id = (int)($_GET['user_id'] ?? 0);
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        exit();
    }
    
    try {
        // Get user's order history
        $stmt = $pdo->prepare("
            SELECT o.*, 
                   CASE 
                       WHEN o.kategori = 'dewasa' THEN o.jumlah * 50000
                       WHEN o.kategori = 'anak' THEN o.jumlah * 30000
                       WHEN o.kategori = 'keluarga' THEN o.jumlah * 120000
                   END as total_price
            FROM orders o 
            WHERE o.user_id = ?
            ORDER BY o.waktu_pesan DESC
            LIMIT 10
        ");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format orders
        foreach ($orders as &$order) {
            $order['tanggal_formatted'] = date('d F Y', strtotime($order['tanggal']));
            $order['waktu_pesan_formatted'] = date('d F Y H:i', strtotime($order['waktu_pesan']));
            $order['total_price_formatted'] = 'Rp ' . number_format($order['total_price'], 0, ',', '.');
            $order['status_info'] = getStatusInfo($order['status']);
            $order['category_info'] = getCategoryInfo($order['kategori']);
        }
        
        echo json_encode([
            'success' => true,
            'orders' => $orders,
            'count' => count($orders)
        ]);
        
    } catch (Exception $e) {
        error_log("Get user orders error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error retrieving user orders']);
    }
}
?> 