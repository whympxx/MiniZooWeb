<?php
session_start();
require_once 'includes/db.php';

// Auto-setup admin session
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

// Get quick stats
$stats = [];
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $stats['total'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as pending FROM orders WHERE status = 'pending'");
    $stats['pending'] = $stmt->fetch()['pending'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as paid FROM orders WHERE status = 'paid'");
    $stats['paid'] = $stmt->fetch()['paid'];
} catch (Exception $e) {
    $stats = ['error' => $e->getMessage()];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Hub - Zoo Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .btn-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .btn-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold text-gray-800 mb-4">🦁 Export Hub</h1>
            <p class="text-xl text-gray-600 mb-4">Sistem Manajemen Tiket Kebun Binatang</p>
            <div class="inline-block bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded-lg">
                ✅ Admin Session: <?= htmlspecialchars($_SESSION['username']) ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="card bg-white p-6 rounded-xl shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Pesanan</p>
                        <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['total'] ?? 0) ?></p>
                    </div>
                </div>
            </div>

            <div class="card bg-white p-6 rounded-xl shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Menunggu Konfirmasi</p>
                        <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['pending'] ?? 0) ?></p>
                    </div>
                </div>
            </div>

            <div class="card bg-white p-6 rounded-xl shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Dikonfirmasi</p>
                        <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['paid'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Beautiful Export Interface -->
            <div class="card bg-white p-8 rounded-xl shadow-lg">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">🎨 Beautiful Interface</h3>
                    <p class="text-gray-600 mb-6">Interface export yang cantik dengan statistik real-time dan filter lengkap</p>
                    <a href="export_interface.php" class="btn-primary text-white px-6 py-3 rounded-lg font-medium inline-block w-full">
                        Buka Interface Cantik
                    </a>
                </div>
            </div>

            <!-- Quick Test -->
            <div class="card bg-white p-8 rounded-xl shadow-lg">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-teal-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">⚡ Quick Test</h3>
                    <p class="text-gray-600 mb-6">Test cepat export functionality dengan hasil langsung</p>
                    <a href="quick_test_export.php" class="btn-success text-white px-6 py-3 rounded-lg font-medium inline-block w-full">
                        Test Cepat
                    </a>
                </div>
            </div>

            <!-- Debug Interface -->
            <div class="card bg-white p-8 rounded-xl shadow-lg">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-cyan-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">🔧 Debug Interface</h3>
                    <p class="text-gray-600 mb-6">Interface debug lengkap untuk troubleshooting export</p>
                    <a href="test_export_debug.php" class="btn-info text-white px-6 py-3 rounded-lg font-medium inline-block w-full">
                        Debug Lengkap
                    </a>
                </div>
            </div>

            <!-- Simple Test -->
            <div class="card bg-white p-8 rounded-xl shadow-lg">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">📝 Simple Test</h3>
                    <p class="text-gray-600 mb-6">Test sederhana dengan form dan API test</p>
                    <a href="test_export_simple.php" class="btn-warning text-white px-6 py-3 rounded-lg font-medium inline-block w-full">
                        Test Sederhana
                    </a>
                </div>
            </div>

            <!-- Add Sample Data -->
            <div class="card bg-white p-8 rounded-xl shadow-lg">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">➕ Add Sample Data</h3>
                    <p class="text-gray-600 mb-6">Tambah data sample untuk testing export</p>
                    <a href="add_sample_data.php" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-medium inline-block w-full transition-colors">
                        Tambah Data Sample
                    </a>
                </div>
            </div>

            <!-- Admin Dashboard -->
            <div class="card bg-white p-8 rounded-xl shadow-lg">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-gray-500 to-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">🏠 Admin Dashboard</h3>
                    <p class="text-gray-600 mb-6">Dashboard admin utama sistem</p>
                    <a href="admin/admin_dashboard.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium inline-block w-full transition-colors">
                        Buka Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-12 text-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Quick Actions</h2>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="pages/Login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    🔐 Login Manual
                </a>
                <a href="check_database.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    📊 Check Database
                </a>
                <a href="setup_admin.php" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    ⚙️ Setup Admin
                </a>
            </div>
        </div>
    </div>
</body>
</html> 