<?php
// Simple test file to verify export functionality
session_start();

// Set admin session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'test_admin';

// Include the export file
require_once 'pages/tiket_export.php';

// Test the statistics endpoint
if (isset($_GET['test_stats'])) {
    $_GET['stats'] = true;
    // This will trigger the statistics code in tiket_export.php
    exit();
}

// Test export functionality
if (isset($_GET['test_export'])) {
    $_POST['action'] = 'export_all';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    // This will trigger the export code in tiket_export.php
    exit();
}

echo "Test file loaded successfully. Use ?test_stats or ?test_export to test functionality.";
?> 