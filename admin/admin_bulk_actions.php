<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_ids = $_POST['user_ids'] ?? [];
    
    if (empty($user_ids) || !is_array($user_ids)) {
        echo json_encode(['success' => false, 'message' => 'Pilih user terlebih dahulu']);
        exit();
    }
    
    // Filter out admin users to prevent self-deletion
    $current_user_id = $_SESSION['user_id'];
    $user_ids = array_filter($user_ids, function($id) use ($current_user_id) {
        return $id != $current_user_id;
    });
    
    if (empty($user_ids)) {
        echo json_encode(['success' => false, 'message' => 'Tidak ada user yang valid untuk diproses']);
        exit();
    }
    
    $placeholders = str_repeat('?,', count($user_ids) - 1) . '?';
    
    try {
        switch ($action) {
            case 'bulk_activate':
                $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE id IN ($placeholders) AND role != 'admin'");
                $stmt->execute($user_ids);
                $affected = $stmt->rowCount();
                echo json_encode(['success' => true, 'message' => "$affected user berhasil diaktifkan"]);
                break;
                
            case 'bulk_suspend':
                $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id IN ($placeholders) AND role != 'admin'");
                $stmt->execute($user_ids);
                $affected = $stmt->rowCount();
                echo json_encode(['success' => true, 'message' => "$affected user berhasil disuspend"]);
                break;
                
            case 'bulk_delete':
                // First check if users have orders
                $stmt = $pdo->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id IN ($placeholders)");
                $stmt->execute($user_ids);
                $order_count = $stmt->fetch()['order_count'];
                
                if ($order_count > 0) {
                    echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus user yang memiliki pesanan. Hapus pesanan terlebih dahulu.']);
                    exit();
                }
                
                $stmt = $pdo->prepare("DELETE FROM users WHERE id IN ($placeholders) AND role != 'admin'");
                $stmt->execute($user_ids);
                $affected = $stmt->rowCount();
                echo json_encode(['success' => true, 'message' => "$affected user berhasil dihapus"]);
                break;
                
            case 'bulk_change_role':
                $new_role = $_POST['new_role'] ?? 'user';
                if (!in_array($new_role, ['user', 'admin'])) {
                    echo json_encode(['success' => false, 'message' => 'Role tidak valid']);
                    exit();
                }
                
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id IN ($placeholders) AND role != 'admin'");
                $params = array_merge([$new_role], $user_ids);
                $stmt->execute($params);
                $affected = $stmt->rowCount();
                echo json_encode(['success' => true, 'message' => "Role $affected user berhasil diubah menjadi $new_role"]);
                break;
                
            case 'bulk_export':
                $stmt = $pdo->prepare("SELECT id, username, email, phone, role, is_active, created_at FROM users WHERE id IN ($placeholders) ORDER BY created_at DESC");
                $stmt->execute($user_ids);
                $users = $stmt->fetchAll();
                
                $format = $_POST['format'] ?? 'csv';
                
                if ($format === 'csv') {
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="bulk_users_export_' . date('Y-m-d_H-i-s') . '.csv"');
                    
                    $output = fopen('php://output', 'w');
                    fputcsv($output, ['ID', 'Username', 'Email', 'Phone', 'Role', 'Status', 'Created At']);
                    
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
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'data' => $users]);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal melakukan aksi: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?> 