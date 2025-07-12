<?php
/**
 * Setup Admin Account - Zoo Management System
 * Script untuk membuat akun admin secara otomatis
 */

echo "🦁 Zoo Management System - Setup Admin Account\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Include database connection
    require_once 'includes/db.php';
    
    echo "✅ Database connection successful!\n";
    
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    $admin_count = $stmt->fetchColumn();
    
    if ($admin_count > 0) {
        echo "⚠️  Admin account already exists!\n";
        
        // Show existing admins
        $stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE role = 'admin'");
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n📋 Existing Admin Accounts:\n";
        foreach ($admins as $admin) {
            echo "   👤 {$admin['username']} ({$admin['email']}) - Created: {$admin['created_at']}\n";
        }
        
        echo "\n💡 You can use any of these accounts to login.\n";
        echo "   Default password for all accounts: password\n\n";
        
    } else {
        echo "📝 Creating default admin account...\n";
        
        // Create default admin
        $admin_data = [
            'username' => 'Admin Zoo',
            'email' => 'admin@zoo.com',
            'phone' => '081234567890',
            'role' => 'admin',
            'password' => password_hash('password', PASSWORD_DEFAULT)
        ];
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, role, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(array_values($admin_data));
        
        echo "✅ Admin account created successfully!\n\n";
        echo "🔑 Login Credentials:\n";
        echo "   Email: admin@zoo.com\n";
        echo "   Password: password\n";
        echo "   Role: admin\n\n";
    }
    
    echo "🌐 Login URLs:\n";
    echo "   User Login: http://localhost/Tugas13/pages/Login.php\n";
    echo "   Admin Dashboard: http://localhost/Tugas13/admin/admin_dashboard.php\n\n";
    
    echo "📋 Next Steps:\n";
    echo "   1. Go to http://localhost/Tugas13/pages/Login.php\n";
    echo "   2. Login with admin credentials\n";
    echo "   3. You will be redirected to admin dashboard\n";
    echo "   4. Change the default password for security\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    echo "💡 Troubleshooting:\n";
    echo "   1. Make sure XAMPP MySQL is running\n";
    echo "   2. Check database credentials in config.php\n";
    echo "   3. Run database/setup_database.sql first\n";
    echo "   4. Make sure database 'zoo_management' exists\n";
}

echo str_repeat("=", 50) . "\n";
echo "Setup completed!\n";
?> 