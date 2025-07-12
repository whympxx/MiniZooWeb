<?php
echo "🔍 Checking Database and Data...\n";
echo "================================\n\n";

try {
    require_once 'includes/db.php';
    echo "✅ Database connection successful!\n\n";
    
    // Check orders table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $result = $stmt->fetch();
    echo "📊 Total orders in database: " . $result['count'] . "\n";
    
    if ($result['count'] > 0) {
        // Show sample orders
        $stmt = $pdo->query("SELECT * FROM orders LIMIT 3");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "\n📋 Sample orders:\n";
        foreach ($orders as $order) {
            echo "- ID: {$order['id']}, Status: {$order['status']}, Kategori: {$order['kategori']}, Jumlah: {$order['jumlah']}\n";
        }
    } else {
        echo "⚠️  No orders found in database!\n";
    }
    
    // Check users table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "\n👥 Total users in database: " . $result['count'] . "\n";
    
    // Check table structure
    echo "\n📋 Orders table structure:\n";
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n================================\n";
echo "Check completed!\n";
?> 