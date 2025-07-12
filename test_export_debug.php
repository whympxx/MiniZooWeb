<?php
/**
 * Debug Export Test Script
 * Script untuk debug export tiket step by step
 */

echo "<h1>🔧 Debug Export Tiket Zoo</h1>";
echo "<hr>";

// Setup admin session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'admin';

echo "<p>✅ Admin session created: " . $_SESSION['username'] . "</p>";

// Test database connection
echo "<h2>📊 Step 1: Database Connection Test</h2>";
try {
    require_once 'includes/db.php';
    echo "<p style='color: green;'>✅ Database connected successfully</p>";
    
    // Check orders count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $result = $stmt->fetch();
    echo "<p>📊 Total orders: " . $result['count'] . "</p>";
    
    if ($result['count'] > 0) {
        // Show orders with user info
        $stmt = $pdo->query("
            SELECT o.*, u.username, u.email as user_email, u.phone as user_phone
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.waktu_pesan DESC
        ");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📋 Orders with User Info:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Status</th><th>Kategori</th><th>Jumlah</th><th>Tanggal</th></tr>";
        foreach ($orders as $order) {
            echo "<tr>";
            echo "<td>{$order['id']}</td>";
            echo "<td>{$order['username']}</td>";
            echo "<td>{$order['status']}</td>";
            echo "<td>{$order['kategori']}</td>";
            echo "<td>{$order['jumlah']}</td>";
            echo "<td>{$order['tanggal']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test export directory
echo "<h2>📁 Step 2: Export Directory Test</h2>";
$export_dir = __DIR__ . '/exports';
echo "<p>Export directory: " . $export_dir . "</p>";

if (!is_dir($export_dir)) {
    if (mkdir($export_dir, 0755, true)) {
        echo "<p style='color: green;'>✅ Export directory created</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create export directory</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Export directory exists</p>";
}

// Test CSV generation
echo "<h2>📄 Step 3: CSV Generation Test</h2>";
if (isset($orders) && !empty($orders)) {
    try {
        // Generate CSV content
        $csv_content = "ID Pesanan,Username,Email User,Telepon User,Nama Pemesan,Email Pemesan,Tanggal Kunjungan,Jumlah Tiket,Kategori Tiket,Status Pesanan,Metode Pembayaran,Harga per Tiket,Total Harga,Waktu Pesan,Waktu Bayar\n";
        
        foreach ($orders as $order) {
            // Calculate price
            $price_per_ticket = 0;
            switch($order['kategori']) {
                case 'dewasa':
                    $price_per_ticket = 50000;
                    break;
                case 'anak':
                    $price_per_ticket = 30000;
                    break;
                case 'keluarga':
                    $price_per_ticket = 120000;
                    break;
            }
            $total_price = $price_per_ticket * $order['jumlah'];
            
            // Format dates
            $tanggal = !empty($order['tanggal']) ? date('d/m/Y', strtotime($order['tanggal'])) : '';
            $waktu_pesan = !empty($order['waktu_pesan']) ? date('d/m/Y H:i', strtotime($order['waktu_pesan'])) : '';
            $waktu_bayar = !empty($order['waktu_bayar']) ? date('d/m/Y H:i', strtotime($order['waktu_bayar'])) : '';
            
            // Format status
            $status_labels = [
                'pending' => 'Menunggu Konfirmasi',
                'paid' => 'Dikonfirmasi',
                'failed' => 'Ditolak'
            ];
            $status = $status_labels[$order['status']] ?? $order['status'];
            
            // Format category
            $category_labels = [
                'dewasa' => 'Dewasa',
                'anak' => 'Anak-anak',
                'keluarga' => 'Keluarga'
            ];
            $category = $category_labels[$order['kategori']] ?? $order['kategori'];
            
            $csv_content .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s,%d,%s,%s,%s,%s,%s,%s,%s\n",
                $order['id'],
                escapeCSV($order['username'] ?? ''),
                escapeCSV($order['user_email'] ?? ''),
                escapeCSV($order['user_phone'] ?? ''),
                escapeCSV($order['nama'] ?? ''),
                escapeCSV($order['email'] ?? ''),
                $tanggal,
                $order['jumlah'] ?? 0,
                $category,
                $status,
                escapeCSV($order['metode_pembayaran'] ?? ''),
                number_format($price_per_ticket, 0, ',', '.'),
                number_format($total_price, 0, ',', '.'),
                $waktu_pesan,
                $waktu_bayar
            );
        }
        
        echo "<p style='color: green;'>✅ CSV content generated successfully</p>";
        echo "<p>CSV length: " . strlen($csv_content) . " characters</p>";
        
        // Show preview
        echo "<h3>📋 CSV Preview:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 200px; overflow-y: auto;'>";
        echo htmlspecialchars(substr($csv_content, 0, 1000)) . "...";
        echo "</pre>";
        
        // Test file writing
        $filename = 'test_export_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = $export_dir . '/' . $filename;
        
        if (file_put_contents($filepath, $csv_content) !== false) {
            echo "<p style='color: green;'>✅ File written successfully: " . $filename . "</p>";
            echo "<p>File size: " . filesize($filepath) . " bytes</p>";
            
            // Test download link
            $download_url = 'includes/download_file.php?type=export&file=' . urlencode($filename);
            echo "<p><a href='$download_url' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📥 Test Download</a></p>";
            
        } else {
            echo "<p style='color: red;'>❌ Failed to write file</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ CSV generation error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No orders to export</p>";
}

// Test actual export function
echo "<h2>🚀 Step 4: Actual Export Function Test</h2>";
echo "<button onclick='testActualExport()' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Export Function</button>";
echo "<div id='exportResult'></div>";

function escapeCSV($value) {
    if ($value === null) {
        return '';
    }
    $value = (string)$value;
    if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
        return '"' . str_replace('"', '""', $value) . '"';
    }
    return $value;
}
?>

<script>
function testActualExport() {
    const resultDiv = document.getElementById('exportResult');
    resultDiv.innerHTML = '⏳ Testing export function...';
    
    fetch('pages/tiket_export.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=export_all'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = `
                <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;">
                    <h3 style="color: #155724; margin-top: 0;">✅ Export Successful!</h3>
                    <p><strong>Message:</strong> ${data.message}</p>
                    <p><strong>Count:</strong> ${data.count}</p>
                    <p><strong>Filename:</strong> ${data.filename}</p>
                    ${data.download_url ? `<a href="${data.download_url}" style="background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 3px; display: inline-block; margin-top: 10px;">📥 Download File</a>` : ''}
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 5px;">
                    <h3 style="color: #721c24; margin-top: 0;">❌ Export Failed!</h3>
                    <p><strong>Error:</strong> ${data.message}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        resultDiv.innerHTML = `
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 5px;">
                <h3 style="color: #721c24; margin-top: 0;">❌ Network Error!</h3>
                <p><strong>Error:</strong> ${error.message}</p>
            </div>
        `;
    });
}
</script>

<hr>
<p><a href="export_interface.php" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🎨 Go to Export Interface</a></p> 