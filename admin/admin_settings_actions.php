<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'backup_database':
            backupDatabase();
            break;
        case 'get_system_info':
            getSystemInfo();
            break;
        case 'test_email':
            testEmail();
            break;
        case 'clear_logs':
            clearLogs();
            break;
        case 'export_settings':
            exportSettings();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function backupDatabase() {
    try {
        // Create backup directory if it doesn't exist
        $backup_dir = 'backups/';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        // Generate backup filename
        $backup_file = $backup_dir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Get database configuration
        $host = 'localhost';
        $dbname = 'auth_system';
        $username = 'root';
        $password = '';
        
        // Create backup command
        $command = "mysqldump --host=$host --user=$username --password=$password $dbname > $backup_file";
        
        // Execute backup
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            $download_url = 'download_file.php?type=backup&file=' . basename($backup_file);
            echo json_encode([
                'success' => true, 
                'message' => 'Database backup berhasil dibuat!',
                'file' => $backup_file,
                'download_url' => $download_url
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Gagal membuat backup database'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

function getSystemInfo() {
    global $pdo;
    
    try {
        // Get database statistics
        $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
        $total_users = $stmt->fetch()['total_users'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
        $total_orders = $stmt->fetch()['total_orders'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'");
        $pending_orders = $stmt->fetch()['pending_orders'];
        
        // Get system information
        $system_info = [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_size' => getDatabaseSize(),
            'disk_free_space' => formatBytes(disk_free_space('.')),
            'memory_usage' => formatBytes(memory_get_usage(true)),
            'total_users' => $total_users,
            'total_orders' => $total_orders,
            'pending_orders' => $pending_orders,
            'last_backup' => getLastBackupTime()
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $system_info
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

function testEmail() {
    $email = $_POST['email'] ?? '';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email tidak valid'
        ]);
        return;
    }
    
    try {
        // Simple email test (you might want to use PHPMailer or similar)
        $subject = 'Test Email dari Sistem Zoo';
        $message = 'Ini adalah email test untuk memverifikasi konfigurasi email sistem.';
        $headers = 'From: noreply@zoosurabaya.com' . "\r\n" .
                   'Reply-To: noreply@zoosurabaya.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        
        if (mail($email, $subject, $message, $headers)) {
            echo json_encode([
                'success' => true,
                'message' => 'Email test berhasil dikirim ke ' . $email
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal mengirim email test'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

function clearLogs() {
    try {
        // Clear email logs
        if (file_exists('email_logs.txt')) {
            file_put_contents('email_logs.txt', '');
        }
        
        // Clear other log files if they exist
        $log_files = ['error_log.txt', 'access_log.txt', 'system_log.txt'];
        foreach ($log_files as $log_file) {
            if (file_exists($log_file)) {
                file_put_contents($log_file, '');
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Log sistem berhasil dibersihkan!'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

function exportSettings() {
    try {
        // Get current settings
        $settings = [
            'ticket_prices' => $_SESSION['ticket_prices'] ?? [],
            'system_settings' => $_SESSION['system_settings'] ?? [],
            'notification_settings' => $_SESSION['notification_settings'] ?? [],
            'export_date' => date('Y-m-d H:i:s')
        ];
        
        // Create export directory if it doesn't exist
        $export_dir = 'exports/';
        if (!is_dir($export_dir)) {
            mkdir($export_dir, 0755, true);
        }
        
        // Generate export filename
        $export_file = $export_dir . 'settings_export_' . date('Y-m-d_H-i-s') . '.json';
        
        // Save settings to file
        file_put_contents($export_file, json_encode($settings, JSON_PRETTY_PRINT));
        
        $download_url = 'download_file.php?type=export&file=' . basename($export_file);
        echo json_encode([
            'success' => true,
            'message' => 'Pengaturan berhasil diexport!',
            'file' => $export_file,
            'download_url' => $download_url
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

function getDatabaseSize() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT 
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
            FROM information_schema.tables 
            WHERE table_schema = 'auth_system'");
        $result = $stmt->fetch();
        return $result['size_mb'] . ' MB';
    } catch (Exception $e) {
        return 'Unknown';
    }
}

function getLastBackupTime() {
    $backup_dir = 'backups/';
    if (!is_dir($backup_dir)) {
        return 'Never';
    }
    
    $files = glob($backup_dir . '*.sql');
    if (empty($files)) {
        return 'Never';
    }
    
    $latest_file = max($files);
    return date('Y-m-d H:i:s', filemtime($latest_file));
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
?> 