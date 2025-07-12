<?php
/**
 * Database Connection Test
 * Script untuk menguji konektivitas database dan tabel yang diperlukan
 */

echo "🔍 Testing Database Connection...\n\n";

try {
    // Include database connection
    require_once 'includes/db.php';
    
    echo "✅ Database connection successful!\n";
    echo "📊 Database: " . DB_NAME . "\n";
    echo "🏠 Host: " . DB_HOST . "\n\n";
    
    // Test tables
    echo "🔍 Checking required tables...\n\n";
    
    $tables = ['users', 'orders'];
    $allTablesExist = true;
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        
        if ($stmt && $stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists\n";
            
            // Count records
            $countStmt = $pdo->prepare("SELECT COUNT(*) as count FROM `$table`");
            $countStmt->execute();
            $count = $countStmt->fetch()['count'];
            echo "   📊 Records: $count\n";
            
        } else {
            echo "❌ Table '$table' does not exist\n";
            $allTablesExist = false;
        }
    }
    
    echo "\n";
    
    if ($allTablesExist) {
        echo "✅ All required tables exist!\n\n";
        
        // Test user authentication data
        echo "🔍 Checking user data...\n";
        $userStmt = $pdo->prepare("SELECT username, email, role FROM users LIMIT 5");
        $userStmt->execute();
        $users = $userStmt->fetchAll();
        
        if (count($users) > 0) {
            echo "✅ Found " . count($users) . " users:\n";
            foreach ($users as $user) {
                echo "   👤 {$user['username']} ({$user['email']}) - {$user['role']}\n";
            }
        } else {
            echo "⚠️  No users found. You may need to run database/setup_database.sql\n";
        }
        
    } else {
        echo "❌ Some tables are missing. Please run database/setup_database.sql\n";
    }
    
    echo "\n🎉 Database test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "💡 Troubleshooting steps:\n";
    echo "1. Make sure XAMPP MySQL is running\n";
    echo "2. Check database credentials in config.php\n";
    echo "3. Run database/setup_database.sql to create tables\n";
    echo "4. Make sure database '" . DB_NAME . "' exists\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Database Configuration:\n";
echo "Host: " . DB_HOST . "\n";
echo "Database: " . DB_NAME . "\n";
echo "Username: " . DB_USERNAME . "\n";
echo "Debug Mode: " . (DEBUG_MODE ? 'ON' : 'OFF') . "\n";
echo str_repeat("=", 50) . "\n";
?>
