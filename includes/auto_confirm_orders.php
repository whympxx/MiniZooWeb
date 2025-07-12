<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Handle auto-confirmation requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'auto_confirm_all':
            autoConfirmAllPendingOrders();
            break;
        case 'auto_confirm_filtered':
            autoConfirmFilteredOrders();
            break;
        case 'get_pending_count':
            getPendingOrdersCount();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit();
    }
}

function autoConfirmAllPendingOrders() {
    global $pdo;
    
    try {
        // Get all pending orders
        $stmt = $pdo->prepare("
            SELECT o.*, u.username, u.email as user_email
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.status = 'pending'
            ORDER BY o.waktu_pesan ASC
        ");
        $stmt->execute();
        $pending_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($pending_orders)) {
            echo json_encode(['success' => false, 'message' => 'No pending orders found']);
            return;
        }
        
        $confirmed_count = 0;
        $failed_count = 0;
        
        foreach ($pending_orders as $order) {
            try {
                // Update order status
                $stmt = $pdo->prepare("UPDATE orders SET status = 'paid', waktu_bayar = NOW() WHERE id = ?");
                $stmt->execute([$order['id']]);
                
                if ($stmt->rowCount() > 0) {
                    $confirmed_count++;
                    
                    // Send notification if enabled
                    if (isset($_SESSION['notification_settings']['email_notifications']) && $_SESSION['notification_settings']['email_notifications']) {
                        require_once 'notification_system.php';
                        sendOrderConfirmationNotification($order['id']);
                    }
                } else {
                    $failed_count++;
                }
            } catch (Exception $e) {
                $failed_count++;
                error_log("Auto confirm order {$order['id']} error: " . $e->getMessage());
            }
        }
        
        // Log the auto-confirmation
        logAutoConfirmation('all_pending', $confirmed_count, $failed_count, count($pending_orders));
        
        echo json_encode([
            'success' => true,
            'message' => "Auto-confirmation completed. Confirmed: $confirmed_count, Failed: $failed_count",
            'confirmed' => $confirmed_count,
            'failed' => $failed_count,
            'total' => count($pending_orders)
        ]);
        
    } catch (Exception $e) {
        error_log("Auto confirm all orders error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error during auto-confirmation']);
    }
}

function autoConfirmFilteredOrders() {
    global $pdo;
    
    // Get filter parameters
    $category = $_POST['category'] ?? '';
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';
    $max_orders = (int)($_POST['max_orders'] ?? 50);
    
    try {
        // Build query with filters
        $query = "
            SELECT o.*, u.username, u.email as user_email
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.status = 'pending'
        ";
        $params = [];
        
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
        
        $query .= " ORDER BY o.waktu_pesan ASC LIMIT ?";
        $params[] = $max_orders;
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $pending_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($pending_orders)) {
            echo json_encode(['success' => false, 'message' => 'No pending orders found with the specified filters']);
            return;
        }
        
        $confirmed_count = 0;
        $failed_count = 0;
        
        foreach ($pending_orders as $order) {
            try {
                // Update order status
                $stmt = $pdo->prepare("UPDATE orders SET status = 'paid', waktu_bayar = NOW() WHERE id = ?");
                $stmt->execute([$order['id']]);
                
                if ($stmt->rowCount() > 0) {
                    $confirmed_count++;
                    
                    // Send notification if enabled
                    if (isset($_SESSION['notification_settings']['email_notifications']) && $_SESSION['notification_settings']['email_notifications']) {
                        require_once 'notification_system.php';
                        sendOrderConfirmationNotification($order['id']);
                    }
                } else {
                    $failed_count++;
                }
            } catch (Exception $e) {
                $failed_count++;
                error_log("Auto confirm filtered order {$order['id']} error: " . $e->getMessage());
            }
        }
        
        // Log the auto-confirmation
        logAutoConfirmation('filtered', $confirmed_count, $failed_count, count($pending_orders), [
            'category' => $category,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'max_orders' => $max_orders
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => "Auto-confirmation completed. Confirmed: $confirmed_count, Failed: $failed_count",
            'confirmed' => $confirmed_count,
            'failed' => $failed_count,
            'total' => count($pending_orders),
            'filters' => [
                'category' => $category,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'max_orders' => $max_orders
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Auto confirm filtered orders error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error during auto-confirmation']);
    }
}

function getPendingOrdersCount() {
    global $pdo;
    
    try {
        // Get total pending count
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
        $stmt->execute();
        $total_pending = $stmt->fetch()['total'];
        
        // Get pending count by category
        $stmt = $pdo->prepare("
            SELECT kategori, COUNT(*) as count 
            FROM orders 
            WHERE status = 'pending' 
            GROUP BY kategori
        ");
        $stmt->execute();
        $pending_by_category = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get pending count by date (last 7 days)
        $stmt = $pdo->prepare("
            SELECT DATE(waktu_pesan) as date, COUNT(*) as count 
            FROM orders 
            WHERE status = 'pending' 
            AND waktu_pesan >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(waktu_pesan)
            ORDER BY date DESC
        ");
        $stmt->execute();
        $pending_by_date = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'total_pending' => $total_pending,
            'pending_by_category' => $pending_by_category,
            'pending_by_date' => $pending_by_date
        ]);
        
    } catch (Exception $e) {
        error_log("Get pending count error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error retrieving pending count']);
    }
}

function logAutoConfirmation($type, $confirmed_count, $failed_count, $total_count, $filters = null) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => 'auto_confirmation',
        'type' => $type,
        'admin_id' => $_SESSION['user_id'],
        'admin_username' => $_SESSION['username'] ?? 'unknown',
        'confirmed_count' => $confirmed_count,
        'failed_count' => $failed_count,
        'total_count' => $total_count,
        'filters' => $filters
    ];
    
    $log_file = 'logs/auto_confirmations.log';
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
}

// Handle GET requests for auto-confirmation statistics
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['stats'])) {
    try {
        // Get auto-confirmation statistics
        $log_file = 'logs/auto_confirmations.log';
        $stats = [
            'total_auto_confirmations' => 0,
            'total_confirmed' => 0,
            'total_failed' => 0,
            'recent_activity' => []
        ];
        
        if (file_exists($log_file)) {
            $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $recent_lines = array_slice($lines, -20); // Get last 20 entries
            
            foreach ($recent_lines as $line) {
                $log_entry = json_decode($line, true);
                if ($log_entry) {
                    $stats['total_auto_confirmations']++;
                    $stats['total_confirmed'] += $log_entry['confirmed_count'];
                    $stats['total_failed'] += $log_entry['failed_count'];
                    $stats['recent_activity'][] = $log_entry;
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
        
    } catch (Exception $e) {
        error_log("Get auto-confirmation stats error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error retrieving statistics']);
    }
}
?> 