<?php
/**
 * Quick Export Test
 * Test cepat untuk export tiket
 */

echo "<h1>🚀 Quick Export Test</h1>";
echo "<hr>";

// Setup admin session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'admin';

echo "<p>✅ Admin session: " . $_SESSION['username'] . "</p>";

// Test database
try {
    require_once 'includes/db.php';
    
    // Check orders
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $total = $stmt->fetch()['count'];
    echo "<p>📊 Total orders: $total</p>";
    
    if ($total > 0) {
        // Test export
        echo "<h2>📤 Testing Export...</h2>";
        
        // Simulate POST request
        $_POST['action'] = 'export_all';
        
        // Capture output
        ob_start();
        include 'pages/tiket_export.php';
        $output = ob_get_clean();
        
        echo "<h3>Export Response:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        echo htmlspecialchars($output);
        echo "</pre>";
        
        // Parse JSON response
        $response = json_decode($output, true);
        if ($response && isset($response['success'])) {
            if ($response['success']) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
                echo "<h3 style='color: #155724; margin-top: 0;'>✅ Export Successful!</h3>";
                echo "<p><strong>Message:</strong> " . $response['message'] . "</p>";
                echo "<p><strong>Count:</strong> " . $response['count'] . "</p>";
                echo "<p><strong>Filename:</strong> " . $response['filename'] . "</p>";
                if (isset($response['download_url'])) {
                    echo "<a href='" . $response['download_url'] . "' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;'>📥 Download File</a>";
                }
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
                echo "<h3 style='color: #721c24; margin-top: 0;'>❌ Export Failed!</h3>";
                echo "<p><strong>Error:</strong> " . $response['message'] . "</p>";
                echo "</div>";
            }
        } else {
            echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
            echo "<h3 style='color: #856404; margin-top: 0;'>⚠️ Invalid Response</h3>";
            echo "<p>Response is not valid JSON</p>";
            echo "</div>";
        }
        
    } else {
        echo "<p style='color: orange;'>⚠️ No orders to export</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='export_interface.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🎨 Go to Export Interface</a></p>";
echo "<p><a href='test_export_debug.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Go to Debug Interface</a></p>";
?> 