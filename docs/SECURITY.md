# 🔒 Security Guide

## Overview

This security guide outlines the comprehensive security measures implemented in the Zoo Management System and provides guidelines for maintaining secure operations.

## Security Architecture

### Defense in Depth

The Zoo Management System implements a multi-layered security approach:

```
┌─────────────────────────┐
│     User Interface      │ ← Input Validation, CSRF Protection
├─────────────────────────┤
│   Application Layer     │ ← Authentication, Authorization
├─────────────────────────┤
│     Business Logic      │ ← Data Validation, Encryption
├─────────────────────────┤
│     Database Layer      │ ← Prepared Statements, Access Control
├─────────────────────────┤
│   Infrastructure        │ ← Firewall, SSL/TLS, Monitoring
└─────────────────────────┘
```

## Authentication & Authorization

### User Authentication

#### Password Security

```php
// Password hashing using bcrypt
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT, [
        'cost' => 12
    ]);
}

// Password verification
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Password strength validation
function validate_password_strength($password) {
    $requirements = [
        'length' => strlen($password) >= 8,
        'uppercase' => preg_match('/[A-Z]/', $password),
        'lowercase' => preg_match('/[a-z]/', $password),
        'number' => preg_match('/[0-9]/', $password),
        'special' => preg_match('/[^A-Za-z0-9]/', $password)
    ];
    
    return array_filter($requirements);
}
```

#### Session Management

```php
// Secure session configuration
function configure_session() {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', 3600); // 1 hour
    
    session_start();
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } else if (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Session timeout check
function check_session_timeout() {
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        header('Location: /pages/Login.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}
```

#### Multi-Factor Authentication (Optional)

```php
// TOTP implementation for 2FA
function generate_2fa_secret() {
    return base32_encode(random_bytes(20));
}

function verify_2fa_token($secret, $token) {
    require_once 'vendor/autoload.php';
    $authenticator = new OTPHP\TOTP($secret);
    return $authenticator->verify($token, null, 30); // 30-second window
}
```

### Authorization

#### Role-Based Access Control (RBAC)

```php
class PermissionManager {
    private static $roles = [
        'user' => [
            'view_own_profile',
            'edit_own_profile',
            'book_tickets',
            'view_own_bookings'
        ],
        'admin' => [
            'view_all_users',
            'edit_users',
            'manage_bookings',
            'view_analytics',
            'system_settings',
            'backup_database'
        ]
    ];
    
    public static function hasPermission($user_role, $permission) {
        return in_array($permission, self::$roles[$user_role] ?? []);
    }
    
    public static function requirePermission($permission) {
        if (!self::hasPermission($_SESSION['role'] ?? 'guest', $permission)) {
            http_response_code(403);
            die('Access denied');
        }
    }
}

// Usage example
PermissionManager::requirePermission('manage_bookings');
```

## Input Validation & Sanitization

### Data Validation

```php
class InputValidator {
    
    public static function sanitizeInput($input, $type = 'string') {
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'string':
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    public static function validateLength($input, $min, $max) {
        $length = strlen($input);
        return $length >= $min && $length <= $max;
    }
}

// Usage example
$email = InputValidator::sanitizeInput($_POST['email'], 'email');
if (!InputValidator::validateEmail($email)) {
    throw new InvalidArgumentException('Invalid email format');
}
```

### XSS Prevention

```php
// Output encoding
function safe_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Content Security Policy headers
function set_csp_headers() {
    header("Content-Security-Policy: default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
           "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
           "img-src 'self' data: https:; " .
           "font-src 'self' https://fonts.gstatic.com; " .
           "connect-src 'self'; " .
           "frame-ancestors 'none';");
}
```

## CSRF Protection

### Token-Based Protection

```php
class CSRFProtection {
    
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validateToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function requireValidToken() {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        
        if (!self::validateToken($token)) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
    }
    
    public static function getTokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

// Usage in forms
echo CSRFProtection::getTokenField();

// Validation on form submission
CSRFProtection::requireValidToken();
```

## SQL Injection Prevention

### Prepared Statements

```php
class SecureDatabase {
    private $pdo;
    
    public function __construct($dsn, $username, $password) {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $this->pdo = new PDO($dsn, $username, $password, $options);
    }
    
    public function select($query, $params = []) {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($query);
        
        return $stmt->execute($data);
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = [];
        foreach (array_keys($data) as $key) {
            $setClause[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setClause);
        
        $query = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $stmt = $this->pdo->prepare($query);
        
        return $stmt->execute(array_merge($data, $whereParams));
    }
}

// Usage example
$db = new SecureDatabase($dsn, $username, $password);
$users = $db->select(
    "SELECT * FROM users WHERE email = ? AND status = ?", 
    [$email, 'active']
);
```

## File Upload Security

### Secure File Handling

```php
class SecureFileUpload {
    
    private static $allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif'
    ];
    
    private static $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'gif'
    ];
    
    private static $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    public static function validateFile($file) {
        $errors = [];
        
        // Check file was uploaded
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed';
            return $errors;
        }
        
        // Check file size
        if ($file['size'] > self::$maxFileSize) {
            $errors[] = 'File too large';
        }
        
        // Check MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, self::$allowedTypes)) {
            $errors[] = 'Invalid file type';
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::$allowedExtensions)) {
            $errors[] = 'Invalid file extension';
        }
        
        // Check for malicious content
        if (self::containsMaliciousCode($file['tmp_name'])) {
            $errors[] = 'File contains malicious content';
        }
        
        return $errors;
    }
    
    private static function containsMaliciousCode($filePath) {
        $content = file_get_contents($filePath);
        $maliciousPatterns = [
            '/<\?php/',
            '/<script/',
            '/javascript:/',
            '/vbscript:/',
            '/onload=/',
            '/onerror=/'
        ];
        
        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    public static function moveUploadedFile($file, $destination) {
        $errors = self::validateFile($file);
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        // Generate secure filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $fullPath = $destination . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        return $filename;
    }
}
```

## Rate Limiting

### Brute Force Protection

```php
class RateLimiter {
    
    public static function checkLimit($identifier, $action, $maxAttempts = 5, $timeWindow = 900) {
        $key = "rate_limit_{$action}_{$identifier}";
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }
        
        $data = $_SESSION[$key];
        
        // Reset if time window has passed
        if (time() - $data['first_attempt'] > $timeWindow) {
            $_SESSION[$key] = [
                'attempts' => 1,
                'first_attempt' => time()
            ];
            return true;
        }
        
        // Check if limit exceeded
        if ($data['attempts'] >= $maxAttempts) {
            $remaining = $timeWindow - (time() - $data['first_attempt']);
            throw new Exception("Rate limit exceeded. Try again in {$remaining} seconds.");
        }
        
        // Increment attempts
        $_SESSION[$key]['attempts']++;
        return true;
    }
    
    public static function resetLimit($identifier, $action) {
        $key = "rate_limit_{$action}_{$identifier}";
        unset($_SESSION[$key]);
    }
}

// Usage example
try {
    RateLimiter::checkLimit($_SERVER['REMOTE_ADDR'], 'login');
    // Process login attempt
    RateLimiter::resetLimit($_SERVER['REMOTE_ADDR'], 'login'); // Reset on success
} catch (Exception $e) {
    http_response_code(429);
    die($e->getMessage());
}
```

## Encryption & Data Protection

### Sensitive Data Encryption

```php
class DataEncryption {
    
    private static function getKey() {
        return base64_decode($_ENV['ENCRYPTION_KEY'] ?? 'your-base64-encoded-key');
    }
    
    public static function encrypt($data) {
        $key = self::getKey();
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    public static function decrypt($encryptedData) {
        $key = self::getKey();
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
}

// Usage for sensitive fields
$encryptedPhone = DataEncryption::encrypt($phoneNumber);
$decryptedPhone = DataEncryption::decrypt($encryptedPhone);
```

## Security Headers

### HTTP Security Headers

```php
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // HTTPS enforcement (only in production)
    if (APP_ENV === 'production') {
        header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
    }
    
    // Feature policy
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// Call at the beginning of each page
setSecurityHeaders();
```

## Logging & Monitoring

### Security Event Logging

```php
class SecurityLogger {
    
    private static $logFile = '/var/log/zoo-management/security.log';
    
    public static function logEvent($event, $details = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $_SESSION['user_id'] ?? null,
            'details' => $details
        ];
        
        $logLine = json_encode($logEntry) . PHP_EOL;
        file_put_contents(self::$logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    public static function logLoginAttempt($email, $success) {
        self::logEvent('login_attempt', [
            'email' => $email,
            'success' => $success
        ]);
    }
    
    public static function logPrivilegeEscalation($action) {
        self::logEvent('privilege_escalation_attempt', [
            'action' => $action
        ]);
    }
    
    public static function logSuspiciousActivity($description) {
        self::logEvent('suspicious_activity', [
            'description' => $description
        ]);
    }
}

// Usage examples
SecurityLogger::logLoginAttempt($email, false);
SecurityLogger::logSuspiciousActivity('Multiple failed password attempts');
```

## Vulnerability Assessment

### Security Checklist

#### Application Security
- [ ] All user inputs validated and sanitized
- [ ] SQL injection protection implemented
- [ ] XSS prevention measures in place
- [ ] CSRF tokens on all forms
- [ ] Secure session management
- [ ] Password hashing with bcrypt
- [ ] File upload restrictions enforced
- [ ] Error messages don't reveal sensitive info

#### Infrastructure Security
- [ ] SSL/TLS certificates properly configured
- [ ] Security headers implemented
- [ ] Database access restricted
- [ ] File permissions properly set
- [ ] Sensitive files protected
- [ ] Backup security configured
- [ ] Monitoring and logging enabled

#### Access Control
- [ ] Role-based access control implemented
- [ ] Admin panel properly secured
- [ ] Default credentials changed
- [ ] Account lockout mechanisms
- [ ] Session timeout configured
- [ ] Two-factor authentication (optional)

### Penetration Testing

#### Automated Testing Tools

```bash
# OWASP ZAP for web application security testing
docker run -t owasp/zap2docker-stable zap-baseline.py -t http://your-domain.com

# SQLMap for SQL injection testing
sqlmap -u "http://your-domain.com/login.php" --forms --dbs

# Nikto for web server scanning
nikto -h http://your-domain.com
```

#### Manual Testing Checklist

1. **Authentication Testing**
   - Test for weak passwords
   - Verify session management
   - Check for privilege escalation

2. **Input Validation Testing**
   - SQL injection attempts
   - XSS payload injection
   - File upload bypass attempts

3. **Authorization Testing**
   - Direct object reference
   - Function level access control
   - Role-based restrictions

## Incident Response

### Security Incident Plan

```php
class IncidentResponse {
    
    public static function handleSecurityIncident($type, $details) {
        // Log the incident
        SecurityLogger::logEvent('security_incident', [
            'type' => $type,
            'details' => $details
        ]);
        
        // Send alerts
        self::sendSecurityAlert($type, $details);
        
        // Take automated actions
        switch ($type) {
            case 'brute_force':
                self::blockIP($_SERVER['REMOTE_ADDR']);
                break;
            case 'sql_injection':
                self::lockDownDatabase();
                break;
            case 'privilege_escalation':
                self::suspendUser($_SESSION['user_id'] ?? null);
                break;
        }
    }
    
    private static function sendSecurityAlert($type, $details) {
        $message = "Security incident detected: {$type}\n";
        $message .= "Details: " . json_encode($details) . "\n";
        $message .= "Time: " . date('Y-m-d H:i:s') . "\n";
        $message .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
        
        // Send to security team
        mail('security@yourdomain.com', 'Security Alert', $message);
    }
    
    private static function blockIP($ip) {
        // Implement IP blocking logic
        file_put_contents('/etc/banned_ips.txt', $ip . PHP_EOL, FILE_APPEND);
    }
}
```

## Compliance & Standards

### Data Protection Compliance

#### GDPR Compliance Features
- User consent management
- Data portability (export user data)
- Right to be forgotten (data deletion)
- Data breach notification
- Privacy by design implementation

#### Implementation Example

```php
class GDPRCompliance {
    
    public static function exportUserData($userId) {
        $userData = [
            'personal_info' => self::getUserInfo($userId),
            'bookings' => self::getUserBookings($userId),
            'login_history' => self::getLoginHistory($userId)
        ];
        
        return json_encode($userData, JSON_PRETTY_PRINT);
    }
    
    public static function deleteUserData($userId) {
        // Anonymize instead of delete for audit purposes
        $anonymizedData = [
            'username' => 'deleted_user_' . $userId,
            'email' => 'deleted@example.com',
            'full_name' => 'Deleted User',
            'phone' => null,
            'deleted_at' => date('Y-m-d H:i:s')
        ];
        
        // Update user record
        $db = new SecureDatabase($dsn, $username, $password);
        $db->update('users', $anonymizedData, 'id = ?', [$userId]);
        
        SecurityLogger::logEvent('user_data_deletion', ['user_id' => $userId]);
    }
}
```

---

*Security Guide last updated: December 2024*

**Note**: Security is an ongoing process. Regularly update dependencies, monitor for vulnerabilities, and conduct security audits to maintain the highest level of protection.
