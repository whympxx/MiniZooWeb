<?php
session_start();
require_once 'includes/db.php';

// Set up admin session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Test the add user functionality
echo "Testing Add User Functionality...\n";

// Simulate POST data
$_POST = [
    'action' => 'add_user',
    'username' => 'testuser_' . time(),
    'email' => 'testuser_' . time() . '@example.com',
    'phone' => '08123456789',
    'password' => 'testpass123',
    'role' => 'user'
];

// Capture output
ob_start();

// Include the admin actions file
require_once 'admin/admin_actions.php';

$output = ob_get_clean();
echo "Response: " . $output . "\n";

echo "Test completed.\n";
?> 