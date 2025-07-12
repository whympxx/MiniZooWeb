<?php
session_start();
include 'db.php';

echo "<h2>Testing Admin Login</h2>";

// Test admin credentials
$email = 'admin@example.com';
$password = 'password';

$query = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($query);

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
    echo "<p>User found: " . $user['username'] . "</p>";
    echo "<p>Role: " . $user['role'] . "</p>";
    echo "<p>Is Active: " . $user['is_active'] . "</p>";
    
    if (password_verify($password, $user['password'])) {
        echo "<p style='color: green;'>Password verification successful!</p>";
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        
        echo "<p>Session variables set:</p>";
        echo "<ul>";
        echo "<li>user_id: " . $_SESSION['user_id'] . "</li>";
        echo "<li>role: " . $_SESSION['role'] . "</li>";
        echo "<li>username: " . $_SESSION['username'] . "</li>";
        echo "</ul>";
        
        if ($user['role'] === 'admin') {
            echo "<p style='color: blue;'>This user is an admin and should be redirected to admin_dashboard.php</p>";
        } else {
            echo "<p style='color: orange;'>This user is not an admin and should be redirected to dashboard.php</p>";
        }
    } else {
        echo "<p style='color: red;'>Password verification failed!</p>";
    }
} else {
    echo "<p style='color: red;'>User not found!</p>";
}

echo "<hr>";
echo "<p><a href='Login.php'>Go to Login Page</a></p>";
echo "<p><a href='admin_dashboard.php'>Go to Admin Dashboard</a></p>";
?> 