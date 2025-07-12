<?php
/**
 * Fix Admin Account - Zoo Management System
 * Web interface untuk memperbaiki akun admin
 */

session_start();
require_once 'includes/db.php';

$message = '';
$error = '';
$admin_info = [];

try {
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        $error = 'Table users tidak ditemukan! Jalankan database/setup_database.sql terlebih dahulu.';
    } else {
        // Get admin accounts
        $stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE role = 'admin'");
        $stmt->execute();
        $admin_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create_admin':
                $admin_data = [
                    'username' => 'Admin Zoo',
                    'email' => 'admin@zoo.com',
                    'phone' => '081234567890',
                    'role' => 'admin',
                    'password' => password_hash('password', PASSWORD_DEFAULT)
                ];
                
                $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, role, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute(array_values($admin_data));
                
                $message = 'Akun admin berhasil dibuat! Email: admin@zoo.com, Password: password';
                break;
                
            case 'fix_password':
                $admin_id = (int)($_POST['admin_id'] ?? 0);
                if ($admin_id > 0) {
                    $fixed_password = password_hash('password', PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'admin'");
                    $stmt->execute([$fixed_password, $admin_id]);
                    
                    $message = 'Password admin berhasil diperbaiki! Password baru: password';
                }
                break;
                
            case 'delete_admin':
                $admin_id = (int)($_POST['admin_id'] ?? 0);
                if ($admin_id > 0) {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
                    $stmt->execute([$admin_id]);
                    
                    $message = 'Akun admin berhasil dihapus!';
                }
                break;
        }
        
        // Refresh admin list
        $stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE role = 'admin'");
        $stmt->execute();
        $admin_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Admin Account - Zoo Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-12 w-12 bg-indigo-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-tools text-white text-xl"></i>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Fix Admin Account
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Zoo Management System - Admin Account Management
                </p>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Database Status -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-database mr-2"></i>
                    Database Status
                </h3>
                
                <?php if (empty($error)): ?>
                    <div class="text-green-600">
                        <i class="fas fa-check-circle mr-2"></i>
                        Database connection successful
                    </div>
                <?php else: ?>
                    <div class="text-red-600">
                        <i class="fas fa-times-circle mr-2"></i>
                        Database connection failed
                    </div>
                <?php endif; ?>
            </div>

            <!-- Admin Accounts -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-users mr-2"></i>
                        Admin Accounts
                    </h3>
                    
                    <?php if (empty($admin_info)): ?>
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="create_admin">
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                                <i class="fas fa-plus mr-1"></i>
                                Create Default Admin
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (empty($admin_info)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-user-slash text-4xl mb-4"></i>
                        <p>No admin accounts found</p>
                        <p class="text-sm mt-2">Click "Create Default Admin" to create one</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($admin_info as $admin): ?>
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">
                                            <?= htmlspecialchars($admin['username']) ?>
                                        </h4>
                                        <p class="text-sm text-gray-500">
                                            <?= htmlspecialchars($admin['email']) ?>
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            Created: <?= date('d/m/Y H:i', strtotime($admin['created_at'])) ?>
                                        </p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="fix_password">
                                            <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                                                <i class="fas fa-key mr-1"></i>
                                                Fix Password
                                            </button>
                                        </form>
                                        
                                        <?php if (count($admin_info) > 1): ?>
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this admin?')">
                                                <input type="hidden" name="action" value="delete_admin">
                                                <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                                    <i class="fas fa-trash mr-1"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-bolt mr-2"></i>
                    Quick Actions
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="pages/Login.php" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-3 rounded-lg text-center transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Go to Login
                    </a>
                    
                    <a href="admin/admin_dashboard.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg text-center transition-colors">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Admin Dashboard
                    </a>
                    
                    <a href="test_database.php" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-3 rounded-lg text-center transition-colors">
                        <i class="fas fa-database mr-2"></i>
                        Test Database
                    </a>
                    
                    <a href="setup_admin.php" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-lg text-center transition-colors">
                        <i class="fas fa-cog mr-2"></i>
                        Setup Admin
                    </a>
                </div>
            </div>

            <!-- Login Info -->
            <?php if (!empty($admin_info)): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-medium text-blue-900 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        Login Information
                    </h4>
                    <div class="text-sm text-blue-700 space-y-1">
                        <p><strong>Email:</strong> <?= htmlspecialchars($admin_info[0]['email']) ?></p>
                        <p><strong>Password:</strong> password</p>
                        <p><strong>Login URL:</strong> <a href="pages/Login.php" class="underline">pages/Login.php</a></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.bg-green-100, .bg-red-100');
            messages.forEach(function(message) {
                message.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html> 