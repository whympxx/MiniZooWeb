<?php
/**
 * Add Sample Data for Export Testing
 * Script untuk menambahkan data sample tiket untuk testing export
 */

echo "🦁 Adding Sample Ticket Data...\n";
echo "================================\n\n";

try {
    require_once 'includes/db.php';
    echo "✅ Database connected successfully!\n\n";
    
    // Check if we have users first
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $user_count = $stmt->fetch()['count'];
    
    if ($user_count == 0) {
        echo "⚠️  No users found. Creating sample users first...\n";
        
        // Create sample users
        $users = [
            ['username' => 'john_doe', 'email' => 'john@example.com', 'phone' => '081234567890', 'role' => 'user'],
            ['username' => 'jane_smith', 'email' => 'jane@example.com', 'phone' => '081234567891', 'role' => 'user'],
            ['username' => 'bob_wilson', 'email' => 'bob@example.com', 'phone' => '081234567892', 'role' => 'user']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, role, password) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($users as $user) {
            $user['password'] = password_hash('password123', PASSWORD_DEFAULT);
            $stmt->execute(array_values($user));
            echo "✅ Created user: {$user['username']}\n";
        }
    }
    
    // Get user IDs for orders
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'user' LIMIT 3");
    $user_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($user_ids)) {
        echo "❌ No users available for creating orders\n";
        exit;
    }
    
    // Check current orders count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $order_count = $stmt->fetch()['count'];
    
    echo "📊 Current orders: $order_count\n";
    echo "🔄 Adding sample data automatically...\n\n";
    
    // Create sample orders
    $sample_orders = [
        [
            'user_id' => $user_ids[0],
            'nama' => 'John Doe',
            'email' => 'john@example.com',
            'tanggal' => date('Y-m-d', strtotime('+1 day')),
            'jumlah' => 2,
            'kategori' => 'dewasa',
            'status' => 'paid',
            'metode_pembayaran' => 'Transfer Bank',
            'waktu_pesan' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            'waktu_bayar' => date('Y-m-d H:i:s', strtotime('-1 hour'))
        ],
        [
            'user_id' => $user_ids[1],
            'nama' => 'Jane Smith',
            'email' => 'jane@example.com',
            'tanggal' => date('Y-m-d', strtotime('+3 days')),
            'jumlah' => 1,
            'kategori' => 'anak',
            'status' => 'pending',
            'metode_pembayaran' => 'E-Wallet',
            'waktu_pesan' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
            'waktu_bayar' => null
        ],
        [
            'user_id' => $user_ids[2],
            'nama' => 'Bob Wilson',
            'email' => 'bob@example.com',
            'tanggal' => date('Y-m-d', strtotime('+1 week')),
            'jumlah' => 1,
            'kategori' => 'keluarga',
            'status' => 'paid',
            'metode_pembayaran' => 'Cash',
            'waktu_pesan' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'waktu_bayar' => date('Y-m-d H:i:s', strtotime('-12 hours'))
        ],
        [
            'user_id' => $user_ids[0],
            'nama' => 'John Doe',
            'email' => 'john@example.com',
            'tanggal' => date('Y-m-d', strtotime('+2 days')),
            'jumlah' => 3,
            'kategori' => 'dewasa',
            'status' => 'failed',
            'metode_pembayaran' => 'Credit Card',
            'waktu_pesan' => date('Y-m-d H:i:s', strtotime('-3 hours')),
            'waktu_bayar' => null
        ],
        [
            'user_id' => $user_ids[1],
            'nama' => 'Jane Smith',
            'email' => 'jane@example.com',
            'tanggal' => date('Y-m-d', strtotime('+5 days')),
            'jumlah' => 2,
            'kategori' => 'anak',
            'status' => 'paid',
            'metode_pembayaran' => 'Transfer Bank',
            'waktu_pesan' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'waktu_bayar' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'user_id' => $user_ids[2],
            'nama' => 'Bob Wilson',
            'email' => 'bob@example.com',
            'tanggal' => date('Y-m-d', strtotime('+1 month')),
            'jumlah' => 4,
            'kategori' => 'keluarga',
            'status' => 'pending',
            'metode_pembayaran' => 'E-Wallet',
            'waktu_pesan' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'waktu_bayar' => null
        ],
        [
            'user_id' => $user_ids[0],
            'nama' => 'John Doe',
            'email' => 'john@example.com',
            'tanggal' => date('Y-m-d', strtotime('+2 weeks')),
            'jumlah' => 1,
            'kategori' => 'dewasa',
            'status' => 'paid',
            'metode_pembayaran' => 'Cash',
            'waktu_pesan' => date('Y-m-d H:i:s', strtotime('-6 hours')),
            'waktu_bayar' => date('Y-m-d H:i:s', strtotime('-5 hours'))
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, nama, email, tanggal, jumlah, kategori, status, metode_pembayaran, waktu_pesan, waktu_bayar) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $created_count = 0;
    foreach ($sample_orders as $order) {
        try {
            $stmt->execute([
                $order['user_id'],
                $order['nama'],
                $order['email'],
                $order['tanggal'],
                $order['jumlah'],
                $order['kategori'],
                $order['status'],
                $order['metode_pembayaran'],
                $order['waktu_pesan'],
                $order['waktu_bayar']
            ]);
            $created_count++;
            echo "✅ Created order: {$order['nama']} - {$order['kategori']} x{$order['jumlah']} ({$order['status']})\n";
        } catch (Exception $e) {
            echo "❌ Failed to create order for {$order['nama']}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📊 Summary:\n";
    echo "✅ Created $created_count sample orders\n";
    
    // Show final statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $total = $stmt->fetch()['total'];
    echo "📈 Total orders in database: $total\n";
    
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    $status_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "📋 Status breakdown:\n";
    foreach ($status_counts as $status) {
        echo "   - {$status['status']}: {$status['count']}\n";
    }
    
    $stmt = $pdo->query("SELECT kategori, COUNT(*) as count FROM orders GROUP BY kategori");
    $category_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "📋 Category breakdown:\n";
    foreach ($category_counts as $category) {
        echo "   - {$category['kategori']}: {$category['count']}\n";
    }
    
    echo "\n🎉 Sample data creation completed!\n";
    echo "You can now test the export functionality.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n================================\n";
echo "Script completed!\n";
?> 