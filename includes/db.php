<?php
/**
 * Database connection configuration
 * Supports both mysqli and PDO connections
 */

// Include main configuration
require_once __DIR__ . '/../config.php';

// Database configuration using constants from config.php
$host = DB_HOST;
$user = DB_USERNAME;
$pass = DB_PASSWORD;
$db   = DB_NAME;

// Create mysqli connection for legacy compatibility
$conn = new mysqli($host, $user, $pass, $db);

// Check mysqli connection
if ($conn->connect_error) {
    if (DEBUG_MODE) {
        die("Database connection failed: " . $conn->connect_error);
    } else {
        die("Database connection failed. Please contact administrator.");
    }
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Create PDO connection for modern features
try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch(PDOException $e) {
    if (DEBUG_MODE) {
        die("PDO connection failed: " . $e->getMessage());
    } else {
        die("Database connection failed. Please contact administrator.");
    }
}

// Helper function for secure queries
function executeQuery($query, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        if (DEBUG_MODE) {
            error_log("Query error: " . $e->getMessage() . " Query: " . $query);
        }
        throw $e;
    }
}

// Helper function for transactions
function executeTransaction($callback) {
    global $pdo;
    try {
        $pdo->beginTransaction();
        $result = $callback($pdo);
        $pdo->commit();
        return $result;
    } catch(Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}
?>
