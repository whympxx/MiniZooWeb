<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_user':
            addNewUser();
            break;
        case 'export_users':
            exportUserData();
            break;
        case 'get_analytics':
            getAnalytics();
            break;
        case 'bulk_action':
            handleBulkAction();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function addNewUser() {
    global $pdo;
    
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username, email, dan password harus diisi']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
        return;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter']);
        return;
    }
    
    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username atau email sudah terdaftar']);
        return;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, password, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $stmt->execute([$username, $email, $phone, $hashed_password, $role]);
        
        echo json_encode(['success' => true, 'message' => 'User berhasil ditambahkan']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan user: ' . $e->getMessage()]);
    }
}

function exportUserData() {
    global $pdo;
    
    $format = $_POST['format'] ?? 'csv';
    $role_filter = $_POST['role_filter'] ?? '';
    $status_filter = $_POST['status_filter'] ?? '';
    
    // Build query
    $query = "SELECT id, username, email, phone, role, is_active, created_at FROM users WHERE 1=1";
    $params = [];
    
    if ($role_filter) {
        $query .= " AND role = ?";
        $params[] = $role_filter;
    }
    
    if ($status_filter) {
        $query .= " AND is_active = ?";
        $params[] = $status_filter;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    
    if ($format === 'csv') {
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d_H-i-s') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, ['ID', 'Username', 'Email', 'Phone', 'Role', 'Status', 'Created At']);
        
        // Add data
        foreach ($users as $user) {
            fputcsv($output, [
                $user['id'],
                $user['username'],
                $user['email'],
                $user['phone'] ?? '',
                $user['role'],
                $user['is_active'] ? 'Active' : 'Inactive',
                $user['created_at']
            ]);
        }
        
        fclose($output);
    } else {
        // JSON format
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $users]);
    }
}

function getAnalytics() {
    global $pdo;
    
    // Get user statistics
    $stats = [];
    
    // Total users by role
    $stmt = $pdo->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $stmt->execute();
    $stats['users_by_role'] = $stmt->fetchAll();
    
    // Users by status
    $stmt = $pdo->prepare("SELECT is_active, COUNT(*) as count FROM users GROUP BY is_active");
    $stmt->execute();
    $stats['users_by_status'] = $stmt->fetchAll();
    
    // Users registered by month (last 12 months)
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month
    ");
    $stmt->execute();
    $stats['users_by_month'] = $stmt->fetchAll();
    
    // Ticket statistics
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    $stmt->execute();
    $stats['tickets_by_status'] = $stmt->fetchAll();
    
    // Recent activity (last 10 users)
    $stmt = $pdo->prepare("
        SELECT username, email, phone, role, is_active, created_at 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $stats['recent_users'] = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $stats]);
}

function handleBulkAction() {
    global $pdo;
    
    $action = $_POST['bulk_action'] ?? '';
    $user_ids = $_POST['user_ids'] ?? [];
    
    if (empty($user_ids) || !is_array($user_ids)) {
        echo json_encode(['success' => false, 'message' => 'Pilih user terlebih dahulu']);
        return;
    }
    
    $placeholders = str_repeat('?,', count($user_ids) - 1) . '?';
    
    try {
        switch ($action) {
            case 'activate':
                $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE id IN ($placeholders) AND role != 'admin'");
                $stmt->execute($user_ids);
                $message = 'User berhasil diaktifkan';
                break;
                
            case 'suspend':
                $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id IN ($placeholders) AND role != 'admin'");
                $stmt->execute($user_ids);
                $message = 'User berhasil disuspend';
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM users WHERE id IN ($placeholders) AND role != 'admin'");
                $stmt->execute($user_ids);
                $message = 'User berhasil dihapus';
                break;
                
            case 'change_role':
                $new_role = $_POST['new_role'] ?? 'user';
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id IN ($placeholders) AND role != 'admin'");
                $params = array_merge([$new_role], $user_ids);
                $stmt->execute($params);
                $message = 'Role user berhasil diubah';
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
                return;
        }
        
        echo json_encode(['success' => true, 'message' => $message]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal melakukan aksi: ' . $e->getMessage()]);
    }
}

// Handle GET requests for data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_users':
            getUsersList();
            break;
        case 'get_user_details':
            getUserDetails();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function getUsersList() {
    global $pdo;
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $search = $_GET['search'] ?? '';
    $role_filter = $_GET['role_filter'] ?? '';
    $status_filter = $_GET['status_filter'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    // Build query
    $where_conditions = [];
    $params = [];
    
    if ($search) {
        $where_conditions[] = "(username LIKE ? OR email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($role_filter) {
        $where_conditions[] = "role = ?";
        $params[] = $role_filter;
    }
    
    if ($status_filter) {
        $where_conditions[] = "status = ?";
        $params[] = $status_filter;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM users $where_clause";
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    
    // Get users
    $query = "SELECT id, username, email, role, status, created_at FROM users $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    
    $total_pages = ceil($total / $limit);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $users,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total,
            'per_page' => $limit
        ]
    ]);
}

function getUserDetails() {
    global $pdo;
    
    $user_id = (int)($_GET['user_id'] ?? 0);
    
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'User ID tidak valid']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT u.*, 
               COUNT(t.id) as total_tickets,
               COUNT(CASE WHEN t.status = 'pending' THEN 1 END) as pending_tickets
        FROM users u
        LEFT JOIN tickets t ON u.id = t.user_id
        WHERE u.id = ?
        GROUP BY u.id
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
        return;
    }
    
    // Get recent tickets
    $stmt = $pdo->prepare("
        SELECT id, ticket_number, status, created_at, total_amount
        FROM tickets 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_tickets = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            'user' => $user,
            'recent_tickets' => $recent_tickets
        ]
    ]);
}
?> 