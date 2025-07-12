<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/Login.php');
    exit();
}

// Get user data for admin
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$admin = $stmt->fetch();

// Get all users for management
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll();

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
$stmt->execute();
$total_users = $stmt->fetch()['total_users'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_admins FROM users WHERE role = 'admin'");
$stmt->execute();
$total_admins = $stmt->fetch()['total_admins'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_tickets FROM orders");
$stmt->execute();
$total_tickets = $stmt->fetch()['total_tickets'];

$stmt = $pdo->prepare("SELECT COUNT(*) as pending_tickets FROM orders WHERE status = 'pending'");
$stmt->execute();
$pending_tickets = $stmt->fetch()['pending_tickets'];

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $target_user_id = $_POST['user_id'];
        
        switch ($_POST['action']) {
            case 'delete_user':
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
                $stmt->execute([$target_user_id]);
                break;
            case 'toggle_active':
                $stmt = $pdo->prepare("UPDATE users SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?");
                $stmt->execute([$target_user_id]);
                break;
            case 'change_role':
                $new_role = $_POST['new_role'];
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$new_role, $target_user_id]);
                break;
        }
        header('Location: admin_dashboard.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Zoo Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/admin-tailwind.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 9999;
            animation: slideInRight 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .notification.success {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .notification.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .notification.warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b-4 border-indigo-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-alt text-3xl text-indigo-600"></i>
                    </div>
                    <div class="hidden md:block ml-4">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="#" class="text-indigo-600 px-3 py-2 rounded-md text-sm font-medium border-b-2 border-indigo-600">Dashboard</a>
                            <a href="admin_analytics.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Analytics</a>
                            <a href="admin_tiket_management.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Manajemen Tiket</a>
                            <a href="admin_settings.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Pengaturan</a>
                            <a href="../pages/dashboard.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Zoo Dashboard</a>
                            <a href="../pages/tiket.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Tiket</a>
                            <a href="../pages/statistik.php" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Statistik</a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700 text-sm">Welcome, <?php echo htmlspecialchars($admin['username']); ?></span>
                        <a href="../pages/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2 admin-title">
                <i class="fas fa-crown text-yellow-500 mr-3"></i>Admin Dashboard
            </h1>
            <p class="text-gray-600 text-lg">Kelola semua pengguna dan sistem dengan kontrol penuh</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $total_users; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-user-shield text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Admins</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $total_admins; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-ticket-alt text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Orders</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $total_tickets; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-clock text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending Orders</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $pending_tickets; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Management Section -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-user-cog text-indigo-600 mr-3"></i>
                    User Management
                </h2>
                <p class="text-gray-600 mt-1">Kelola semua pengguna dalam sistem</p>
            </div>

            <!-- Search and Filter -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" id="searchUser" placeholder="Cari user..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent search-input">
                    </div>
                    <div class="flex gap-2">
                        <select id="roleFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Semua Role</option>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                        <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Semua Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                
                <!-- Bulk Actions -->
                <div class="mt-4 flex flex-col md:flex-row gap-4 items-center">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="selectAll" class="text-sm font-medium text-gray-700">Pilih Semua</label>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        <button id="bulkActivate" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <i class="fas fa-check mr-2"></i>Aktifkan
                        </button>
                        <button id="bulkSuspend" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <i class="fas fa-pause mr-2"></i>Suspend
                        </button>
                        <button id="bulkDelete" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <i class="fas fa-trash mr-2"></i>Hapus
                        </button>
                        <button id="bulkExport" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <i class="fas fa-download mr-2"></i>Export
                        </button>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAllHeader" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200 user-row" 
                            data-role="<?php echo htmlspecialchars($user['role']); ?>" 
                            data-status="<?php echo htmlspecialchars($user['is_active']); ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($user['id'] != $user_id): ?>
                                <input type="checkbox" class="user-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" value="<?php echo $user['id']; ?>">
                                <?php else: ?>
                                <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <i class="fas fa-user text-indigo-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['username']); ?></div>
                                        <div class="text-sm text-gray-500">ID: <?php echo $user['id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    <?php echo $user['is_active'] == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $user['is_active'] == 1 ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <?php if ($user['id'] != $user_id): ?>
                                    <button onclick="toggleUserStatus(<?php echo $user['id']; ?>, <?php echo $user['is_active']; ?>)" 
                                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200">
                                        <i class="fas fa-toggle-on text-lg"></i>
                                    </button>
                                    <button onclick="changeUserRole(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>')" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-200">
                                        <i class="fas fa-user-edit text-lg"></i>
                                    </button>
                                    <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-200">
                                        <i class="fas fa-trash text-lg"></i>
                                    </button>
                                    <?php else: ?>
                                    <span class="text-gray-400">Current User</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-plus text-xl"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-800">Add New User</h3>
                </div>
                <p class="text-gray-600 mb-4">Buat akun pengguna baru dengan role dan status yang sesuai</p>
                <button onclick="showAddUserModal()" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition duration-300">
                    <i class="fas fa-plus mr-2"></i>Add User
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-ticket-alt text-xl"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-800">Manajemen Tiket</h3>
                </div>
                <p class="text-gray-600 mb-4">Kelola dan konfirmasi pesanan tiket dari pengguna</p>
                <button onclick="window.location.href='admin_tiket_management.php'" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg transition duration-300">
                    <i class="fas fa-ticket-alt mr-2"></i>Kelola Tiket
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-download text-xl"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-800">Export Data</h3>
                </div>
                <p class="text-gray-600 mb-4">Export data pengguna dalam format CSV atau Excel</p>
                <button onclick="exportUserData()" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition duration-300">
                    <i class="fas fa-download mr-2"></i>Export
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-chart-bar text-xl"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-800">Analytics</h3>
                </div>
                <p class="text-gray-600 mb-4">Lihat statistik dan analisis data pengguna</p>
                <button onclick="showAnalytics()" class="w-full bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded-lg transition duration-300">
                    <i class="fas fa-chart-bar mr-2"></i>View Analytics
                </button>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Add User Modal -->
    <div id="addUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Add New User</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="addUserForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone (Optional)</label>
                        <input type="tel" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" class="flex-1 bg-indigo-500 hover:bg-indigo-600 text-white py-2 px-4 rounded-lg transition duration-300">
                            Add User
                        </button>
                        <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg transition duration-300">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-4xl text-yellow-500 mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Konfirmasi</h3>
                    <p id="confirmMessage" class="text-gray-600 mb-6"></p>
                    <div class="flex space-x-3">
                        <button id="confirmYes" class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg transition duration-300">
                            Ya
                        </button>
                        <button onclick="closeConfirmModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg transition duration-300">
                            Tidak
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Search and Filter functionality
        document.getElementById('searchUser').addEventListener('input', filterUsers);
        document.getElementById('roleFilter').addEventListener('change', filterUsers);
        document.getElementById('statusFilter').addEventListener('change', filterUsers);

        function filterUsers() {
            const searchTerm = document.getElementById('searchUser').value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('.user-row');

            rows.forEach(row => {
                const username = row.querySelector('td:first-child').textContent.toLowerCase();
                const role = row.dataset.role;
                const status = row.dataset.status;

                const matchesSearch = username.includes(searchTerm);
                const matchesRole = !roleFilter || role === roleFilter;
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesRole && matchesStatus) {
                    row.style.display = '';
                    row.classList.add('fade-in');
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Modal functions
        function showAddUserModal() {
            document.getElementById('addUserModal').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('modalContent').classList.remove('scale-95', 'opacity-0');
                document.getElementById('modalContent').classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeModal() {
            document.getElementById('modalContent').classList.add('scale-95', 'opacity-0');
            document.getElementById('modalContent').classList.remove('scale-100', 'opacity-100');
            setTimeout(() => {
                document.getElementById('addUserModal').classList.add('hidden');
            }, 300);
        }

        function showConfirmModal(message, action) {
            document.getElementById('confirmMessage').textContent = message;
            document.getElementById('confirmYes').onclick = action;
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.add('hidden');
        }

        // User management functions
        function toggleUserStatus(userId, currentStatus) {
            const action = currentStatus == 1 ? 'suspend' : 'activate';
            
            showConfirmModal(
                `Apakah Anda yakin ingin ${action} user ini?`,
                () => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="toggle_active">
                        <input type="hidden" name="user_id" value="${userId}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            );
        }

        function changeUserRole(userId, currentRole) {
            const newRole = currentRole === 'user' ? 'admin' : 'user';
            
            showConfirmModal(
                `Apakah Anda yakin ingin mengubah role user menjadi ${newRole}?`,
                () => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="change_role">
                        <input type="hidden" name="user_id" value="${userId}">
                        <input type="hidden" name="new_role" value="${newRole}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            );
        }

        function deleteUser(userId, username) {
            showConfirmModal(
                `Apakah Anda yakin ingin menghapus user "${username}"? Tindakan ini tidak dapat dibatalkan.`,
                () => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" value="${userId}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            );
        }

        function exportUserData() {
            // Implementation for exporting user data
            alert('Fitur export akan segera tersedia!');
        }

        function showAnalytics() {
            window.location.href = 'admin_analytics.php';
        }

        // Bulk actions functionality
        const selectAllCheckbox = document.getElementById('selectAll');
        const selectAllHeader = document.getElementById('selectAllHeader');
        const userCheckboxes = document.querySelectorAll('.user-checkbox');
        const bulkButtons = document.querySelectorAll('#bulkActivate, #bulkSuspend, #bulkDelete, #bulkExport');

        function updateBulkButtons() {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            const hasChecked = checkedBoxes.length > 0;
            
            bulkButtons.forEach(button => {
                button.disabled = !hasChecked;
            });
        }

        function getSelectedUserIds() {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            return Array.from(checkedBoxes).map(cb => cb.value);
        }

        // Select all functionality
        selectAllCheckbox.addEventListener('change', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            selectAllHeader.checked = this.checked;
            updateBulkButtons();
        });

        selectAllHeader.addEventListener('change', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            selectAllCheckbox.checked = this.checked;
            updateBulkButtons();
        });

        // Individual checkbox change
        userCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(userCheckboxes).every(cb => cb.checked);
                const anyChecked = Array.from(userCheckboxes).some(cb => cb.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllHeader.checked = allChecked;
                updateBulkButtons();
            });
        });

        // Bulk action handlers
        document.getElementById('bulkActivate').addEventListener('click', function() {
            const userIds = getSelectedUserIds();
            if (userIds.length === 0) return;
            
            showConfirmModal(
                `Apakah Anda yakin ingin mengaktifkan ${userIds.length} user?`,
                () => performBulkAction('bulk_activate', userIds)
            );
        });

        document.getElementById('bulkSuspend').addEventListener('click', function() {
            const userIds = getSelectedUserIds();
            if (userIds.length === 0) return;
            
            showConfirmModal(
                `Apakah Anda yakin ingin suspend ${userIds.length} user?`,
                () => performBulkAction('bulk_suspend', userIds)
            );
        });

        document.getElementById('bulkDelete').addEventListener('click', function() {
            const userIds = getSelectedUserIds();
            if (userIds.length === 0) return;
            
            showConfirmModal(
                `Apakah Anda yakin ingin menghapus ${userIds.length} user? Tindakan ini tidak dapat dibatalkan.`,
                () => performBulkAction('bulk_delete', userIds)
            );
        });

        document.getElementById('bulkExport').addEventListener('click', function() {
            const userIds = getSelectedUserIds();
            if (userIds.length === 0) return;
            
            performBulkAction('bulk_export', userIds);
        });

        function performBulkAction(action, userIds) {
            const formData = new FormData();
            formData.append('action', action);
            userIds.forEach(id => formData.append('user_ids[]', id));
            
            if (action === 'bulk_export') {
                formData.append('format', 'csv');
                // Create a temporary form for download
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin_bulk_actions.php';
                formData.forEach((value, key) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
                return;
            }
            
            fetch('admin_bulk_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Uncheck all checkboxes
                    userCheckboxes.forEach(cb => cb.checked = false);
                    selectAllCheckbox.checked = false;
                    selectAllHeader.checked = false;
                    updateBulkButtons();
                    // Reload page after a short delay
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Terjadi kesalahan: ' + error.message, 'error');
            });
        }

        // Notification function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Add user form submission
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add_user');
            
            fetch('admin_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Terjadi kesalahan: ' + error.message, 'error');
            });
        });

        // Close modals when clicking outside
        document.getElementById('addUserModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeConfirmModal();
            }
        });

        // Initialize bulk buttons state
        updateBulkButtons();
    </script>
</body>
</html> 