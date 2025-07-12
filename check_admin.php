<?php
/**
 * Check Admin Account - Zoo Management System
 * Script untuk mengecek dan memperbaiki akun admin
 */

echo "🔍 Zoo Management System - Admin Account Checker\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Include database connection
    require_once 'includes/db.php';
    
    echo "✅ Database connection successful!\n\n";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Table 'users' does not exist!\n";
        echo "💡 Please run database/setup_database.sql first\n\n";
        exit;
    }
    
    echo "✅ Table 'users' exists\n";
    
    // Check admin accounts
    $stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE role = 'admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($admins)) {
        echo "❌ No admin accounts found!\n\n";
        
        // Ask if user wants to create admin
        echo "🤔 Do you want to create a default admin account? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) === 'y' || trim(strtolower($line)) === 'yes') {
            echo "\n📝 Creating default admin account...\n";
            
            $admin_data = [
                'username' => 'Admin Zoo',
                'email' => 'admin@zoo.com',
                'phone' => '081234567890',
                'role' => 'admin',
                'password' => password_hash('password', PASSWORD_DEFAULT)
            ];
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, role, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(array_values($admin_data));
            
            echo "✅ Admin account created successfully!\n";
            echo "🔑 Login Credentials:\n";
            echo "   Email: admin@zoo.com\n";
            echo "   Password: password\n\n";
        } else {
            echo "❌ No admin account created. Please create one manually.\n\n";
        }
        
    } else {
        echo "✅ Found " . count($admins) . " admin account(s):\n\n";
        
        foreach ($admins as $admin) {
            echo "👤 Admin #{$admin['id']}:\n";
            echo "   Username: {$admin['username']}\n";
            echo "   Email: {$admin['email']}\n";
            echo "   Role: {$admin['role']}\n";
            echo "   Created: {$admin['created_at']}\n";
            echo "   Default Password: password\n\n";
        }
        
        // Test login for each admin
        echo "🧪 Testing admin login...\n";
        foreach ($admins as $admin) {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$admin['id']]);
            $user = $stmt->fetch();
            
            if (password_verify('password', $user['password'])) {
                echo "✅ {$admin['email']} - Login OK\n";
            } else {
                echo "❌ {$admin['email']} - Password mismatch\n";
                
                // Fix password
                echo "🔧 Fixing password for {$admin['email']}...\n";
                $fixed_password = password_hash('password', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$fixed_password, $admin['id']]);
                echo "✅ Password fixed!\n";
            }
        }
    }
    
    // Check total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $total_users = $stmt->fetch()['total'];
    
    echo "\n📊 Database Summary:\n";
    echo "   Total Users: $total_users\n";
    echo "   Admin Users: " . count($admins) . "\n";
    echo "   Regular Users: " . ($total_users - count($admins)) . "\n\n";
    
    echo "🌐 Login URLs:\n";
    echo "   User Login: http://localhost/Tugas13/pages/Login.php\n";
    echo "   Admin Dashboard: http://localhost/Tugas13/admin/admin_dashboard.php\n\n";
    
    echo "🎯 Quick Login Test:\n";
    if (!empty($admins)) {
        $first_admin = $admins[0];
        echo "   Email: {$first_admin['email']}\n";
        echo "   Password: password\n";
        echo "   Expected Redirect: admin_dashboard.php\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    echo "💡 Troubleshooting:\n";
    echo "   1. Make sure XAMPP MySQL is running\n";
    echo "   2. Check database credentials in config.php\n";
    echo "   3. Run database/setup_database.sql first\n";
    echo "   4. Make sure database 'zoo_management' exists\n";
}

echo str_repeat("=", 50) . "\n";
echo "Check completed!\n";
?> 