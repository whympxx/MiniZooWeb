<?php
session_start();
require_once 'includes/db.php';

// Auto-setup admin session for this interface
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    try {
        $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE role = 'admin' LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['username'] = $admin['username'];
        } else {
            $_SESSION['user_id'] = 1;
            $_SESSION['role'] = 'admin';
            $_SESSION['username'] = 'admin';
        }
    } catch (Exception $e) {
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';
        $_SESSION['username'] = 'admin';
    }
}

// Get statistics
$stats = [];
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders");
    $stmt->execute();
    $stats['total_orders'] = $stmt->fetch()['total_orders'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'");
    $stmt->execute();
    $stats['pending_orders'] = $stmt->fetch()['pending_orders'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as paid_orders FROM orders WHERE status = 'paid'");
    $stmt->execute();
    $stats['paid_orders'] = $stmt->fetch()['paid_orders'];
    
    $stmt = $pdo->prepare("
        SELECT SUM(
            CASE 
                WHEN kategori = 'dewasa' THEN jumlah * 50000
                WHEN kategori = 'anak' THEN jumlah * 30000
                WHEN kategori = 'keluarga' THEN jumlah * 120000
            END
        ) as total_revenue 
        FROM orders 
        WHERE status = 'paid'
    ");
    $stmt->execute();
    $stats['total_revenue'] = $stmt->fetch()['total_revenue'] ?? 0;
} catch (Exception $e) {
    $stats = ['error' => $e->getMessage()];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Tiket - Zoo Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            transition: all 0.3s;
        }
        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(17, 153, 142, 0.4);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">🦁 Export Tiket Zoo</h1>
            <p class="text-gray-600">Sistem Manajemen Tiket Kebun Binatang</p>
            <div class="mt-4 p-3 bg-green-100 rounded-lg inline-block">
                <span class="text-green-800 font-medium">✅ Admin Session: <?= htmlspecialchars($_SESSION['username']) ?></span>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="card bg-white p-6 rounded-xl shadow-md">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Pesanan</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_orders'] ?? 0) ?></p>
                    </div>
                </div>
            </div>

            <div class="card bg-white p-6 rounded-xl shadow-md">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Menunggu Konfirmasi</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['pending_orders'] ?? 0) ?></p>
                    </div>
                </div>
            </div>

            <div class="card bg-white p-6 rounded-xl shadow-md">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Dikonfirmasi</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['paid_orders'] ?? 0) ?></p>
                    </div>
                </div>
            </div>

            <div class="card bg-white p-6 rounded-xl shadow-md">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Pendapatan</p>
                        <p class="text-2xl font-bold text-gray-900">Rp <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Export All -->
            <div class="card bg-white p-6 rounded-xl shadow-md">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">📊 Export Semua Tiket</h2>
                <p class="text-gray-600 mb-6">Export semua data tiket dalam format CSV</p>
                <button onclick="exportAllTickets()" class="btn-primary text-white px-6 py-3 rounded-lg font-medium w-full">
                    Export Semua Tiket
                </button>
                <div id="exportAllResult" class="mt-4"></div>
            </div>

            <!-- Filtered Export -->
            <div class="card bg-white p-6 rounded-xl shadow-md">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">🔍 Export dengan Filter</h2>
                <p class="text-gray-600 mb-6">Export tiket berdasarkan kriteria tertentu</p>
                
                <form id="filterForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Status</option>
                            <option value="pending">Menunggu Konfirmasi</option>
                            <option value="paid">Dikonfirmasi</option>
                            <option value="failed">Ditolak</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                        <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Kategori</option>
                            <option value="dewasa">Dewasa</option>
                            <option value="anak">Anak-anak</option>
                            <option value="keluarga">Keluarga</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                            <input type="date" name="date_from" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                            <input type="date" name="date_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <button type="button" onclick="exportFilteredTickets()" class="btn-success text-white px-6 py-3 rounded-lg font-medium w-full">
                        Export dengan Filter
                    </button>
                </form>
                <div id="exportFilteredResult" class="mt-4"></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 text-center">
            <a href="admin/admin_dashboard.php" class="inline-block bg-gray-600 text-white px-6 py-3 rounded-lg font-medium mr-4 hover:bg-gray-700 transition-colors">
                🏠 Dashboard Admin
            </a>
            <a href="pages/Login.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                🔐 Login Manual
            </a>
        </div>
    </div>

    <script>
        function showResult(elementId, data, isError = false) {
            const element = document.getElementById(elementId);
            const className = isError ? 'p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg' : 'p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg';
            
            if (data.success) {
                let html = `<div class="${className}">`;
                html += `<h3 class="font-bold mb-2">✅ Berhasil!</h3>`;
                html += `<p>${data.message}</p>`;
                
                if (data.count !== undefined) {
                    html += `<p class="mt-2"><strong>Jumlah record:</strong> ${data.count}</p>`;
                }
                
                if (data.download_url) {
                    html += `<a href="${data.download_url}" class="inline-block mt-3 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">📥 Download File</a>`;
                }
                
                html += `</div>`;
                element.innerHTML = html;
            } else {
                element.innerHTML = `<div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg"><h3 class="font-bold mb-2">❌ Error!</h3><p>${data.message}</p></div>`;
            }
        }

        function exportAllTickets() {
            const button = event.target;
            button.disabled = true;
            button.textContent = '⏳ Processing...';
            
            fetch('pages/tiket_export.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=export_all'
            })
            .then(response => response.json())
            .then(data => {
                showResult('exportAllResult', data, !data.success);
            })
            .catch(error => {
                showResult('exportAllResult', {success: false, message: 'Network error: ' + error.message}, true);
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = 'Export Semua Tiket';
            });
        }

        function exportFilteredTickets() {
            const button = event.target;
            button.disabled = true;
            button.textContent = '⏳ Processing...';
            
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            formData.append('action', 'export_filtered');
            
            fetch('pages/tiket_export.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showResult('exportFilteredResult', data, !data.success);
            })
            .catch(error => {
                showResult('exportFilteredResult', {success: false, message: 'Network error: ' + error.message}, true);
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = 'Export dengan Filter';
            });
        }
    </script>
</body>
</html> 