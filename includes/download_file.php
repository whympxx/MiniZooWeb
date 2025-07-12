<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo 'Unauthorized access';
    exit();
}

// Get file parameters
$type = $_GET['type'] ?? '';
$file = $_GET['file'] ?? '';

// Validate parameters
if (empty($type) || empty($file)) {
    http_response_code(400);
    echo 'Missing parameters';
    exit();
}

// Security: Prevent directory traversal
$file = basename($file);
$file = str_replace(['..', '/', '\\'], '', $file);

// Define allowed file types and their directories
$allowed_types = [
    'export' => 'exports',
    'backup' => 'backups',
    'log' => 'logs'
];

if (!isset($allowed_types[$type])) {
    http_response_code(400);
    echo 'Invalid file type';
    exit();
}

$directory = $allowed_types[$type];
// Use absolute path from the project root
$filepath = __DIR__ . '/../' . $directory . '/' . $file;

// Check if file exists
if (!file_exists($filepath)) {
    http_response_code(404);
    echo 'File not found: ' . $filepath;
    exit();
}

// Security: Check file extension
$allowed_extensions = [
    'export' => ['csv', 'xlsx'],
    'backup' => ['sql', 'zip'],
    'log' => ['log', 'txt']
];

$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
if (!in_array($extension, $allowed_extensions[$type])) {
    http_response_code(403);
    echo 'Invalid file type';
    exit();
}

// Get file info
$filesize = filesize($filepath);
$filetime = filemtime($filepath);

// Set appropriate headers based on file type
switch ($extension) {
    case 'csv':
        header('Content-Type: text/csv; charset=utf-8');
        break;
    case 'xlsx':
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        break;
    case 'sql':
        header('Content-Type: application/sql');
        break;
    case 'zip':
        header('Content-Type: application/zip');
        break;
    case 'log':
    case 'txt':
        header('Content-Type: text/plain; charset=utf-8');
        break;
    default:
        header('Content-Type: application/octet-stream');
}

// Set download headers
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . $filesize);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $filetime) . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Pragma: no-cache');

// Log the download
logDownload($type, $file, $filesize);

// Output file content
readfile($filepath);
exit();

function logDownload($type, $filename, $filesize) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'admin_id' => $_SESSION['user_id'],
        'admin_username' => $_SESSION['username'] ?? 'unknown',
        'action' => 'download',
        'file_type' => $type,
        'filename' => $filename,
        'filesize' => $filesize,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) {
        if (!mkdir($log_dir, 0755, true)) {
            error_log("Failed to create logs directory");
            return;
        }
    }
    
    $log_file = $log_dir . '/downloads.log';
    if (file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX) === false) {
        error_log("Failed to write download log");
    }
}
?> 