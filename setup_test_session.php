<?php
// Setup test admin session
session_start();

// Set admin session variables
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'admin';

echo "Test admin session created successfully!<br>";
echo "User ID: " . $_SESSION['user_id'] . "<br>";
echo "Role: " . $_SESSION['role'] . "<br>";
echo "Username: " . $_SESSION['username'] . "<br><br>";

echo "You can now test the export functionality:<br>";
echo "<a href='test_export_interface.html'>Open Test Interface</a><br>";
echo "<a href='pages/tiket_export.php?stats=1'>Test Statistics API</a><br>";
echo "<a href='test_export.php?test_stats'>Test Statistics via Test File</a><br>";
echo "<a href='test_export.php?test_export'>Test Export via Test File</a><br>";
?> 