<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Handle backup requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_backup':
            createDatabaseBackup();
            break;
        case 'restore_backup':
            restoreDatabaseBackup();
            break;
        case 'delete_backup':
            deleteBackup();
            break;
        case 'list_backups':
            listBackups();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit();
    }
}

function createDatabaseBackup() {
    global $pdo;
    
    try {
        // Get database configuration
        $host = 'localhost';
        $dbname = 'auth_system';
        $username = 'root';
        $password = '';
        
        // Create backup directory if it doesn't exist
        $backup_dir = 'backups/';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        // Generate backup filename
        $timestamp = date('Y-m-d_H-i-s');
        $backup_filename = "backup_{$dbname}_{$timestamp}.sql";
        $backup_path = $backup_dir . $backup_filename;
        
        // Create backup using mysqldump
        $command = "mysqldump --host={$host} --user={$username}";
        if (!empty($password)) {
            $command .= " --password={$password}";
        }
        $command .= " --single-transaction --routines --triggers {$dbname} > {$backup_path}";
        
        // Execute backup command
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);
        
        if ($return_var !== 0) {
            throw new Exception('Failed to create backup using mysqldump');
        }
        
        // Check if backup file was created and has content
        if (!file_exists($backup_path) || filesize($backup_path) === 0) {
            throw new Exception('Backup file was not created or is empty');
        }
        
        // Get backup file info
        $file_size = filesize($backup_path);
        $file_size_formatted = formatFileSize($file_size);
        
        // Log the backup creation
        logBackupAction('create', $backup_filename, $file_size, 'success');
        
        echo json_encode([
            'success' => true,
            'message' => "Backup berhasil dibuat: {$backup_filename}",
            'filename' => $backup_filename,
            'size' => $file_size_formatted,
            'download_url' => 'download_file.php?type=backup&file=' . $backup_filename
        ]);
        
    } catch (Exception $e) {
        error_log("Create backup error: " . $e->getMessage());
        logBackupAction('create', $backup_filename ?? 'unknown', 0, 'failed', $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error creating backup: ' . $e->getMessage()]);
    }
}

function restoreDatabaseBackup() {
    if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No backup file uploaded or upload error']);
        return;
    }
    
    $uploaded_file = $_FILES['backup_file'];
    $file_extension = strtolower(pathinfo($uploaded_file['name'], PATHINFO_EXTENSION));
    
    // Validate file type
    if ($file_extension !== 'sql') {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only SQL files are allowed.']);
        return;
    }
    
    try {
        // Get database configuration
        $host = 'localhost';
        $dbname = 'auth_system';
        $username = 'root';
        $password = '';
        
        // Create temporary file
        $temp_file = 'backups/temp_restore_' . time() . '.sql';
        if (!move_uploaded_file($uploaded_file['tmp_name'], $temp_file)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        // Create restore command
        $command = "mysql --host={$host} --user={$username}";
        if (!empty($password)) {
            $command .= " --password={$password}";
        }
        $command .= " {$dbname} < {$temp_file}";
        
        // Execute restore command
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);
        
        // Clean up temporary file
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
        
        if ($return_var !== 0) {
            throw new Exception('Failed to restore database');
        }
        
        // Log the restore action
        logBackupAction('restore', $uploaded_file['name'], $uploaded_file['size'], 'success');
        
        echo json_encode([
            'success' => true,
            'message' => 'Database berhasil direstore dari backup: ' . $uploaded_file['name']
        ]);
        
    } catch (Exception $e) {
        error_log("Restore backup error: " . $e->getMessage());
        logBackupAction('restore', $uploaded_file['name'] ?? 'unknown', 0, 'failed', $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error restoring backup: ' . $e->getMessage()]);
    }
}

function deleteBackup() {
    $filename = $_POST['filename'] ?? '';
    
    if (empty($filename)) {
        echo json_encode(['success' => false, 'message' => 'Filename is required']);
        return;
    }
    
    // Security check - only allow SQL files
    if (pathinfo($filename, PATHINFO_EXTENSION) !== 'sql') {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        return;
    }
    
    try {
        $backup_path = 'backups/' . $filename;
        
        if (!file_exists($backup_path)) {
            echo json_encode(['success' => false, 'message' => 'Backup file not found']);
            return;
        }
        
        $file_size = filesize($backup_path);
        
        // Delete the file
        if (unlink($backup_path)) {
            // Log the deletion
            logBackupAction('delete', $filename, $file_size, 'success');
            
            echo json_encode([
                'success' => true,
                'message' => 'Backup berhasil dihapus: ' . $filename
            ]);
        } else {
            throw new Exception('Failed to delete backup file');
        }
        
    } catch (Exception $e) {
        error_log("Delete backup error: " . $e->getMessage());
        logBackupAction('delete', $filename, 0, 'failed', $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error deleting backup: ' . $e->getMessage()]);
    }
}

function listBackups() {
    try {
        $backup_dir = 'backups/';
        $backups = [];
        
        if (is_dir($backup_dir)) {
            $files = glob($backup_dir . '*.sql');
            
            foreach ($files as $file) {
                $filename = basename($file);
                $file_size = filesize($file);
                $file_time = filemtime($file);
                
                $backups[] = [
                    'filename' => $filename,
                    'size' => formatFileSize($file_size),
                    'size_bytes' => $file_size,
                    'created_at' => date('Y-m-d H:i:s', $file_time),
                    'download_url' => 'download_file.php?type=backup&file=' . $filename
                ];
            }
            
            // Sort by creation time (newest first)
            usort($backups, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        }
        
        echo json_encode([
            'success' => true,
            'backups' => $backups,
            'count' => count($backups)
        ]);
        
    } catch (Exception $e) {
        error_log("List backups error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error listing backups']);
    }
}

function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

function logBackupAction($action, $filename, $file_size, $status, $error_message = null) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'filename' => $filename,
        'file_size' => $file_size,
        'status' => $status,
        'admin_id' => $_SESSION['user_id'],
        'admin_username' => $_SESSION['username'] ?? 'unknown',
        'error_message' => $error_message
    ];
    
    $log_file = 'logs/backup_actions.log';
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
}

// Handle GET requests for backup statistics
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['stats'])) {
    try {
        $backup_dir = 'backups/';
        $stats = [
            'total_backups' => 0,
            'total_size' => 0,
            'total_size_formatted' => '0 B',
            'latest_backup' => null,
            'oldest_backup' => null
        ];
        
        if (is_dir($backup_dir)) {
            $files = glob($backup_dir . '*.sql');
            $stats['total_backups'] = count($files);
            
            if (!empty($files)) {
                $total_size = 0;
                $backup_times = [];
                
                foreach ($files as $file) {
                    $file_size = filesize($file);
                    $file_time = filemtime($file);
                    
                    $total_size += $file_size;
                    $backup_times[] = $file_time;
                }
                
                $stats['total_size'] = $total_size;
                $stats['total_size_formatted'] = formatFileSize($total_size);
                $stats['latest_backup'] = date('Y-m-d H:i:s', max($backup_times));
                $stats['oldest_backup'] = date('Y-m-d H:i:s', min($backup_times));
            }
        }
        
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
        
    } catch (Exception $e) {
        error_log("Get backup stats error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error retrieving backup statistics']);
    }
}
?> 