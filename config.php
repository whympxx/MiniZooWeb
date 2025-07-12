<?php
/**
 * Configuration file for Zoo Management System
 * Contains all system-wide configuration settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'auth_system');

// Application Settings
define('APP_NAME', 'Zoo Management System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/Tugas13');

// Directory Paths (relative to root)
define('ASSETS_PATH', 'assets/');
define('CSS_PATH', ASSETS_PATH . 'css/');
define('IMAGES_PATH', ASSETS_PATH . 'images/');
define('JS_PATH', ASSETS_PATH . 'js/');
define('INCLUDES_PATH', 'includes/');
define('PAGES_PATH', 'pages/');
define('ADMIN_PATH', 'admin/');
define('DATABASE_PATH', 'database/');
define('BACKUPS_PATH', 'backups/');
define('EXPORTS_PATH', 'exports/');

// Security Settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('PASSWORD_MIN_LENGTH', 8);

// Email Settings (untuk notifikasi)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@zoo-management.local');
define('FROM_NAME', 'Zoo Management System');

// File Upload Settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// System Status
define('MAINTENANCE_MODE', false);
define('DEBUG_MODE', false);

// Default Settings
define('DEFAULT_TIMEZONE', 'Asia/Jakarta');
define('DEFAULT_LANGUAGE', 'id');
define('DATE_FORMAT', 'Y-m-d H:i:s');

// Set timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// Error reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>
