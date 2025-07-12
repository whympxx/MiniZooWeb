<?php
session_start();
include '../includes/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/Login.php');
    exit();
}

$notif = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['adminwahyu']);
    $email = trim($_POST['adminwahyu@gmail.com']);
    $phone = trim($_POST['081234567890']);
    $password = $_POST['adminwahyu123'];
    $confirm_password = $_POST['adminwahyu123'];

    // Validation
    if ($username === '' || strlen($username) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL) || 
        !preg_match('/^\d{10,}$/', $phone) || strlen($password) < 6 || 
        !preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password) || 
        $password !== $confirm_password) {
        $notif = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">Please fill the form correctly. Password must contain uppercase, lowercase, and number.</div>';
    } else {
        // Check if email already exists
        $cek = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$cek) {
            $notif = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">Database error. Please try again.</div>';
        } else {
            $cek->bind_param("s", $email);
            $cek->execute();
            $cek->store_result();
            if ($cek->num_rows > 0) {
                $notif = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">Email already registered.</div>';
            } else {
                // Check if username already exists
                $cek_username = $conn->prepare("SELECT id FROM users WHERE username = ?");
                if (!$cek_username) {
                    $notif = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">Database error. Please try again.</div>';
                } else {
                    $cek_username->bind_param("s", $username);
                    $cek_username->execute();
                    $cek_username->store_result();
                    if ($cek_username->num_rows > 0) {
                        $notif = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">Username already taken.</div>';
                    } else {
                        // Create admin account
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $role = 'admin';
                        $stmt = $conn->prepare("INSERT INTO users (username, email, phone, role, password) VALUES (?, ?, ?, ?, ?)");
                        if (!$stmt) {
                            $notif = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">Database error. Please try again.</div>';
                        } else {
                            $stmt->bind_param("sssss", $username, $email, $phone, $role, $hashed_password);
                            if ($stmt->execute()) {
                                $notif = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">Admin account created successfully!</div>';
                            } else {
                                $notif = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">Failed to create admin account. Please try again.</div>';
                            }
                            $stmt->close();
                        }
                    }
                    $cek_username->close();
                }
            }
            $cek->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-200 via-blue-100 to-blue-300 flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="flex flex-col items-center px-6 pt-8 pb-4 bg-gradient-to-r from-blue-700 to-blue-500">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                </svg>
                <span class="text-2xl font-bold text-white tracking-wide">Create Admin</span>
            </div>
            <p class="text-blue-100 text-sm">Create new administrator account</p>
        </div>

        <!-- Form -->
        <form method="POST" action="" class="px-8 py-6 space-y-5">
            <?php if ($notif !== '') echo $notif; ?>
            
            <div>
                <label class="block text-blue-900 text-sm font-medium mb-1" for="username">Username</label>
                <input id="username" name="username" type="text" placeholder="Enter username" 
                       class="w-full px-4 py-2 rounded-lg bg-blue-100 text-blue-900 placeholder-blue-400 border border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all duration-300" required />
            </div>
            
            <div>
                <label class="block text-blue-900 text-sm font-medium mb-1" for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="Enter email" 
                       class="w-full px-4 py-2 rounded-lg bg-blue-100 text-blue-900 placeholder-blue-400 border border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all duration-300" required />
            </div>
            
            <div>
                <label class="block text-blue-900 text-sm font-medium mb-1" for="phone">Phone Number</label>
                <input id="phone" name="phone" type="tel" placeholder="e.g. 081234567890" 
                       class="w-full px-4 py-2 rounded-lg bg-blue-100 text-blue-900 placeholder-blue-400 border border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all duration-300" required />
            </div>
            
            <div>
                <label class="block text-blue-900 text-sm font-medium mb-1" for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Enter password" 
                       class="w-full px-4 py-2 rounded-lg bg-blue-100 text-blue-900 placeholder-blue-400 border border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all duration-300" required />
                <p class="text-blue-600 text-xs mt-1">Must contain uppercase, lowercase, and number</p>
            </div>
            
            <div>
                <label class="block text-blue-900 text-sm font-medium mb-1" for="confirm_password">Confirm Password</label>
                <input id="confirm_password" name="confirm_password" type="password" placeholder="Re-enter password" 
                       class="w-full px-4 py-2 rounded-lg bg-blue-100 text-blue-900 placeholder-blue-400 border border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all duration-300" required />
            </div>
            
            <button type="submit" class="w-full py-2 rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold text-lg shadow-md hover:from-blue-600 hover:to-blue-700 hover:shadow-lg transition-colors duration-200">
                Create Admin Account
            </button>
        </form>
        
        <div class="px-8 pb-6 text-center">
            <a href="admin_dashboard.php" class="text-blue-500 text-sm hover:underline">Back to Admin Dashboard</a>
        </div>
    </div>
</body>
</html> 