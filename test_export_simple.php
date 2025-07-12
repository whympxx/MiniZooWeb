<?php
/**
 * Simple Export Test Script
 * Script sederhana untuk test export tiket tanpa masalah autentikasi
 */

echo "<h1>🦁 Test Export Tiket Zoo</h1>";
echo "<hr>";

// Setup admin session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'admin';

echo "<p>✅ Admin session created: " . $_SESSION['username'] . "</p>";

// Test 1: Get Statistics
echo "<h2>📊 Test 1: Get Statistics</h2>";
$stats_url = "pages/tiket_export.php?stats=1";
echo "<p>Testing: <a href='$stats_url' target='_blank'>$stats_url</a></p>";

$stats_response = file_get_contents($stats_url);
echo "<pre>Response: " . htmlspecialchars($stats_response) . "</pre>";

// Test 2: Export All Tickets
echo "<h2>📤 Test 2: Export All Tickets</h2>";
echo "<form method='POST' action='pages/tiket_export.php' target='_blank'>";
echo "<input type='hidden' name='action' value='export_all'>";
echo "<button type='submit'>Export All Tickets</button>";
echo "</form>";

// Test 3: Export Filtered Tickets
echo "<h2>🔍 Test 3: Export Filtered Tickets</h2>";
echo "<form method='POST' action='pages/tiket_export.php' target='_blank'>";
echo "<input type='hidden' name='action' value='export_filtered'>";
echo "<label>Status: <select name='status'>";
echo "<option value=''>All</option>";
echo "<option value='pending'>Pending</option>";
echo "<option value='paid'>Paid</option>";
echo "<option value='failed'>Failed</option>";
echo "</select></label><br><br>";
echo "<label>Category: <select name='category'>";
echo "<option value=''>All</option>";
echo "<option value='dewasa'>Dewasa</option>";
echo "<option value='anak'>Anak</option>";
echo "<option value='keluarga'>Keluarga</option>";
echo "</select></label><br><br>";
echo "<button type='submit'>Export Filtered</button>";
echo "</form>";

// Test 4: Direct API Test
echo "<h2>🔧 Test 4: Direct API Test</h2>";
echo "<button onclick='testAPI()'>Test Export API</button>";
echo "<div id='apiResult'></div>";

echo "<hr>";
echo "<p><a href='export_interface.php'>🎨 Go to Beautiful Export Interface</a></p>";
echo "<p><a href='admin/admin_dashboard.php'>🏠 Go to Admin Dashboard</a></p>";
?>

<script>
function testAPI() {
    const resultDiv = document.getElementById('apiResult');
    resultDiv.innerHTML = '⏳ Testing...';
    
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
                <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px;">
                    <h3>✅ Success!</h3>
                    <p>${data.message}</p>
                    <p>Count: ${data.count}</p>
                    ${data.download_url ? `<a href="${data.download_url}" style="background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;">Download File</a>` : ''}
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px;">
                    <h3>❌ Error!</h3>
                    <p>${data.message}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        resultDiv.innerHTML = `
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px;">
                <h3>❌ Network Error!</h3>
                <p>${error.message}</p>
            </div>
        `;
    });
}
</script> 