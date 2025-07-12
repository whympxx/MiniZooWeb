<?php
/**
 * Zoo Management System - Auto Installer
 * Script untuk instalasi otomatis sistem
 */

echo "🦁 Zoo Management System - Auto Installer\n";
echo str_repeat("=", 50) . "\n\n";

function createDatabase() {
    echo "📊 Creating database...\n";
    
    try {
        // Connect without database name first
        $pdo = new PDO("mysql:host=" . DB_HOST, DB_USERNAME, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✅ Database '" . DB_NAME . "' created successfully!\n";
        
        return true;
    } catch (PDOException $e) {
        echo "❌ Failed to create database: " . $e->getMessage() . "\n";
        return false;
    }
}

function runSQLFile($filename) {
    echo "📋 Running SQL file: $filename\n";
    
    try {
        require_once 'includes/db.php';
        
        $sql = file_get_contents($filename);
        if ($sql === false) {
            echo "❌ Could not read SQL file: $filename\n";
            return false;
        }
        
        // Split SQL commands by semicolon
        $commands = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($commands as $command) {
            if (!empty($command) && !preg_match('/^\s*(--|#)/', $command)) {
                $pdo->exec($command);
            }
        }
        
        echo "✅ SQL file executed successfully!\n";
        return true;
    } catch (Exception $e) {
        echo "❌ Failed to execute SQL file: " . $e->getMessage() . "\n";
        return false;
    }
}

function createDirectories() {
    echo "📁 Creating directories...\n";
    
    $directories = [
        'backups',
        'exports',
        'assets/js',
        'logs'
    ];
    
    $success = true;
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                echo "✅ Created directory: $dir\n";
            } else {
                echo "❌ Failed to create directory: $dir\n";
                $success = false;
            }
        } else {
            echo "✅ Directory exists: $dir\n";
        }
    }
    
    return $success;
}

function setPermissions() {
    echo "🔒 Setting file permissions...\n";
    
    $writableDirectories = [
        'backups',
        'exports',
        'logs'
    ];
    
    foreach ($writableDirectories as $dir) {
        if (is_dir($dir)) {
            if (chmod($dir, 0777)) {
                echo "✅ Set writable permissions for: $dir\n";
            } else {
                echo "⚠️ Could not set permissions for: $dir\n";
            }
        }
    }
    
    return true;
}

function createDefaultAdmin() {
    echo "👤 Creating default admin user...\n";
    
    try {
        require_once 'includes/db.php';
        
        // Check if admin already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            echo "✅ Admin user already exists\n";
            return true;
        }
        
        // Create default admin
        $adminData = [
            'username' => 'admin',
            'email' => 'admin@zoo-management.local',
            'phone' => '08123456789',
            'role' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT)
        ];
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, role, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(array_values($adminData));
        
        echo "✅ Default admin created!\n";
        echo "   Username: admin\n";
        echo "   Email: admin@zoo-management.local\n";
        echo "   Password: admin123\n";
        echo "   ⚠️  Please change the default password after first login!\n";
        
        return true;
    } catch (Exception $e) {
        echo "❌ Failed to create admin user: " . $e->getMessage() . "\n";
        return false;
    }
}

function testInstallation() {
    echo "🧪 Testing installation...\n";
    
    try {
        require_once 'includes/db.php';
        
        // Test database connection
        $pdo->query("SELECT 1");
        echo "✅ Database connection: OK\n";
        
        // Test required tables
        $tables = ['users', 'orders'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            
            if ($stmt && $stmt->rowCount() > 0) {
                echo "✅ Table '$table': OK\n";
            } else {
                echo "❌ Table '$table': MISSING\n";
                return false;
            }
        }
        
        return true;
    } catch (Exception $e) {
        echo "❌ Installation test failed: " . $e->getMessage() . "\n";
        return false;
    }
}

// Main installation process
echo "🚀 Starting installation process...\n\n";

// Include configuration
require_once 'config.php';

$steps = [
    'Create Database' => 'createDatabase',
    'Create Directories' => 'createDirectories',
    'Set Permissions' => 'setPermissions',
    'Run Database Schema' => function() { return runSQLFile('database/setup_database.sql'); },
    'Create Default Admin' => 'createDefaultAdmin',
    'Test Installation' => 'testInstallation'
];

$success = true;
$stepNumber = 1;

foreach ($steps as $stepName => $stepFunction) {
    echo "[$stepNumber/" . count($steps) . "] $stepName\n";
    
    if (is_callable($stepFunction)) {
        $result = $stepFunction();
    } else {
        $result = call_user_func($stepFunction);
    }
    
    if (!$result) {
        $success = false;
        echo "❌ Installation failed at step: $stepName\n";
        break;
    }
    
    echo "\n";
    $stepNumber++;
}

echo str_repeat("=", 50) . "\n";

if ($success) {
    echo "🎉 Installation completed successfully!\n\n";
    echo "📝 Next steps:\n";
    echo "1. Access your application at: " . APP_URL . "\n";
    echo "2. Login with admin credentials (see above)\n";
    echo "3. Change default admin password\n";
    echo "4. Configure system settings\n";
    echo "5. Delete this install.php file for security\n\n";
    echo "📚 Documentation available in docs/ directory\n";
} else {
    echo "❌ Installation failed!\n\n";
    echo "💡 Troubleshooting:\n";
    echo "1. Make sure XAMPP MySQL is running\n";
    echo "2. Check database credentials in config.php\n";
    echo "3. Ensure proper file permissions\n";
    echo "4. Check error messages above\n";
}

echo str_repeat("=", 50) . "\n";
?>
