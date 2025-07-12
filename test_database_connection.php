<?php
// Test database connection
require_once 'includes/db.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test PDO connection
    echo "<h3>PDO Connection Test</h3>";
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ PDO Connection successful<br>";
    
    // Test users table
    echo "<h3>Users Table Test</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✅ Users table exists with " . $result['count'] . " records<br>";
    
    // Test orders table
    echo "<h3>Orders Table Test</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $result = $stmt->fetch();
    echo "✅ Orders table exists with " . $result['count'] . " records<br>";
    
    // Show sample data
    echo "<h3>Sample Orders Data</h3>";
    $stmt = $pdo->query("
        SELECT o.*, u.username 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        LIMIT 5
    ");
    $orders = $stmt->fetchAll();
    
    if (empty($orders)) {
        echo "⚠️ No orders found in database<br>";
        echo "You may need to add some test data first.<br>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Nama</th><th>Email</th><th>Tanggal</th><th>Jumlah</th><th>Kategori</th><th>Status</th></tr>";
        foreach ($orders as $order) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($order['id']) . "</td>";
            echo "<td>" . htmlspecialchars($order['username']) . "</td>";
            echo "<td>" . htmlspecialchars($order['nama']) . "</td>";
            echo "<td>" . htmlspecialchars($order['email']) . "</td>";
            echo "<td>" . htmlspecialchars($order['tanggal']) . "</td>";
            echo "<td>" . htmlspecialchars($order['jumlah']) . "</td>";
            echo "<td>" . htmlspecialchars($order['kategori']) . "</td>";
            echo "<td>" . htmlspecialchars($order['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

echo "<br><a href='setup_test_session.php'>Setup Test Session</a>";
?> 