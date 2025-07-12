<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'list_admins':
            listAdmins();
            break;
        case 'add_admin':
            addAdmin();
            break;
        case 'update_admin':
            updateAdmin();
            break;
        case 'delete_admin':
            deleteAdmin();
            break;
        case 'change_password':
            changePassword();
            break;
        case 'get_admin_details':
            getAdminDetails();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function listAdmins() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, username, email, role, created_at, last_login 
            FROM users 
            WHERE role = 'admin' 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $admins = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'admins' => $admins
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

function addAdmin() {
    global $pdo;
    
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (!$username || !$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
        return;
    }
    
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Password tidak cocok']);
        return;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
        return;
    }
    
    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
            return;
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email sudah digunakan']);
            return;
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new admin
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role, created_at) 
            VALUES (?, ?, ?, 'admin', NOW())
        ");
        $stmt->execute([$username, $email, $hashed_password]);
        
        $admin_id = $pdo->lastInsertId();
        
        // Log admin creation
        $log_entry = date('Y-m-d H:i:s') . " | Admin created: $username ($email) by user ID " . $_SESSION['user_id'] . "\n";
        file_put_contents('admin_logs.txt', $log_entry, FILE_APPEND | LOCK_EX);
        
        echo json_encode([
            'success' => true,
            'message' => 'Admin berhasil ditambahkan!',
            'admin_id' => $admin_id
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

function updateAdmin() {
    global $pdo;
    
    $admin_id = $_POST['admin_id'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if (!$admin_id || !$username || !$email) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
        return;
    }
    
    try {
        // Check if admin exists
        $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ? AND role = 'admin'");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            echo json_encode(['success' => false, 'message' => 'Admin tidak ditemukan']);
            return;
        }
        
        // Check if username already exists (excluding current admin)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $admin_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
            return;
        }
        
        // Check if email already exists (excluding current admin)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $admin_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email sudah digunakan']);
            return;
        }
        
        // Update admin
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ? AND role = 'admin'");
        $stmt->execute([$username, $email, $admin_id]);
        
        // Log admin update
        $log_entry = date('Y-m-d H:i:s') . " | Admin updated: $username ($email) by user ID " . $_SESSION['user_id'] . "\n";
        file_put_contents('admin_logs.txt', $log_entry, FILE_APPEND | LOCK_EX);
        
        echo json_encode([
            'success' => true,
            'message' => 'Admin berhasil diperbarui!'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

function deleteAdmin() {
    global $pdo;
    
    $admin_id = $_POST['admin_id'] ?? '';
    
    if (!$admin_id) {
        echo json_encode(['success' => false, 'message' => 'Admin ID tidak valid']);
        return;
    }
    
    // Prevent self-deletion
    if ($admin_id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus akun sendiri']);
        return;
    }
    
    try {
        // Get admin details for logging
        $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ? AND role = 'admin'");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            echo json_encode(['success' => false, 'message' => 'Admin tidak ditemukan']);
            return;
        }
        
        // Delete admin
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
        $stmt->execute([$admin_id]);
        
        if ($stmt->rowCount() > 0) {
            // Log admin deletion
            $log_entry = date('Y-m-d H:i:s') . " | Admin deleted: " . $admin['username'] . " (" . $admin['email'] . ") by user ID " . $_SESSION['user_id'] . "\n";
            file_put_contents('admin_logs.txt', $log_entry, FILE_APPEND | LOCK_EX);
            
            echo json_encode([
                'success' => true,
                'message' => 'Admin berhasil dihapus!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menghapus admin'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

function changePassword() {
    global $pdo;
    
    $admin_id = $_POST['admin_id'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (!$admin_id || !$current_password || !$new_password || !$confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
        return;
    }
    
    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Password baru tidak cocok']);
        return;
    }
    
    if (strlen($new_password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter']);
        return;
    }
    
    try {
        // Get current password hash
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? AND role = 'admin'");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            echo json_encode(['success' => false, 'message' => 'Admin tidak ditemukan']);
            return;
        }
        
        // Verify current password
        if (!password_verify($current_password, $admin['password'])) {
            echo json_encode(['success' => false, 'message' => 'Password saat ini salah']);
            return;
        }
        
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'admin'");
        $stmt->execute([$hashed_password, $admin_id]);
        
        // Log password change
        $log_entry = date('Y-m-d H:i:s') . " | Admin password changed for ID $admin_id by user ID " . $_SESSION['user_id'] . "\n";
        file_put_contents('admin_logs.txt', $log_entry, FILE_APPEND | LOCK_EX);
        
        echo json_encode([
            'success' => true,
            'message' => 'Password berhasil diubah!'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

function getAdminDetails() {
    global $pdo;
    
    $admin_id = $_POST['admin_id'] ?? '';
    
    if (!$admin_id) {
        echo json_encode(['success' => false, 'message' => 'Admin ID tidak valid']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, username, email, role, created_at, last_login 
            FROM users 
            WHERE id = ? AND role = 'admin'
        ");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            echo json_encode(['success' => false, 'message' => 'Admin tidak ditemukan']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'admin' => $admin
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

// Function to get admin statistics
function getAdminStats() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total admins
        $stmt = $pdo->query("SELECT COUNT(*) as total_admins FROM users WHERE role = 'admin'");
        $stats['total_admins'] = $stmt->fetch()['total_admins'];
        
        // Active admins (logged in within last 30 days)
        $stmt = $pdo->query("
            SELECT COUNT(*) as active_admins 
            FROM users 
            WHERE role = 'admin' 
            AND last_login > DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stats['active_admins'] = $stmt->fetch()['active_admins'];
        
        // Recent admin activities
        $stmt = $pdo->query("
            SELECT COUNT(*) as recent_activities 
            FROM users 
            WHERE role = 'admin' 
            AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stats['recent_activities'] = $stmt->fetch()['recent_activities'];
        
        return $stats;
        
    } catch (Exception $e) {
        return null;
    }
}
?> 