# 🚀 Deployment Guide

## Overview

This guide provides comprehensive instructions for deploying the Zoo Management System to various production environments including shared hosting, VPS, and cloud platforms.

## Pre-Deployment Checklist

### Code Preparation

- [ ] All sensitive data moved to environment variables
- [ ] Debug mode disabled (`DEBUG_MODE = false`)
- [ ] Error reporting configured for production
- [ ] Database credentials secured
- [ ] File permissions properly set
- [ ] All dependencies installed
- [ ] Code tested in staging environment

### Security Review

- [ ] SQL injection protection verified
- [ ] XSS protection implemented
- [ ] CSRF tokens in place
- [ ] Password hashing secure
- [ ] Session configuration hardened
- [ ] File upload restrictions enforced
- [ ] Admin access protected

### Performance Optimization

- [ ] Database queries optimized
- [ ] Static assets minified
- [ ] Caching configured
- [ ] Image compression applied
- [ ] Gzip compression enabled
- [ ] CDN configured (if applicable)

## Environment Setup

### Production Configuration

Create a production configuration file:

**config.production.php**
```php
<?php
// Production Configuration
define('APP_ENV', 'production');
define('DEBUG_MODE', false);
define('APP_URL', 'https://your-domain.com');

// Database (use environment variables)
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'zoo_user');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'zoo_management');

// Security Settings
define('SESSION_COOKIE_SECURE', true);
define('SESSION_COOKIE_HTTPONLY', true);
define('SESSION_COOKIE_SAMESITE', 'Strict');

// Email Configuration
define('SMTP_HOST', $_ENV['SMTP_HOST']);
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME']);
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD']);

// Error Logging
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/logs/php_errors.log');
?>
```

### Environment Variables

Create `.env` file:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=zoo_management_prod
DB_USERNAME=zoo_prod_user
DB_PASSWORD=secure_production_password

# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Email Configuration
SMTP_HOST=smtp.yourdomain.com
SMTP_PORT=587
SMTP_USERNAME=noreply@yourdomain.com
SMTP_PASSWORD=email_password

# Security Keys
SESSION_SECRET_KEY=your_secure_session_key
CSRF_SECRET_KEY=your_csrf_secret_key

# External Services
PAYMENT_GATEWAY_API_KEY=your_payment_api_key
PAYMENT_GATEWAY_SECRET=your_payment_secret
```

## Deployment Methods

### 1. Shared Hosting Deployment

#### Via FTP/SFTP

```bash
# 1. Prepare files locally
zip -r zoo-management.zip . -x "*.git*" "tests/*" "docs/*" ".env"

# 2. Upload via FTP
# Use FileZilla or similar FTP client
# Upload to public_html or www directory

# 3. Extract files on server
unzip zoo-management.zip
```

#### Via cPanel File Manager

1. Access cPanel File Manager
2. Navigate to `public_html`
3. Upload project zip file
4. Extract files
5. Set file permissions

#### File Permissions for Shared Hosting

```bash
# Directories
find . -type d -exec chmod 755 {} \;

# PHP files
find . -name "*.php" -exec chmod 644 {} \;

# Writable directories
chmod 777 backups/
chmod 777 exports/
chmod 755 assets/
```

### 2. VPS/Dedicated Server Deployment

#### Using Apache

**Virtual Host Configuration:**

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/zoo-management
    
    <Directory /var/www/zoo-management>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security headers
    Header always set X-Frame-Options DENY
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"
    
    ErrorLog ${APACHE_LOG_DIR}/zoo-management_error.log
    CustomLog ${APACHE_LOG_DIR}/zoo-management_access.log combined
</VirtualHost>

# SSL Configuration
<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/zoo-management
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    
    # Force HTTPS
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
</VirtualHost>
```

#### Using Nginx

**Nginx Configuration:**

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/zoo-management;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # Security headers
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload";
    
    # PHP Processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Static files caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Deny access to sensitive files
    location ~ /\.(env|git) {
        deny all;
    }
    
    location ~ \.(sql|txt)$ {
        deny all;
    }
}
```

### 3. Cloud Platform Deployment

#### AWS EC2

**User Data Script for Auto-Setup:**

```bash
#!/bin/bash
yum update -y
yum install -y httpd php php-mysqlnd php-gd php-curl php-zip

# Start services
systemctl start httpd
systemctl enable httpd

# Download and setup application
cd /var/www/html
wget https://github.com/your-repo/zoo-management/archive/main.zip
unzip main.zip
mv zoo-management-main/* .
rm -rf zoo-management-main main.zip

# Set permissions
chown -R apache:apache /var/www/html
chmod -R 755 /var/www/html
chmod 777 /var/www/html/backups
chmod 777 /var/www/html/exports

# Configure PHP
echo "date.timezone = Asia/Jakarta" >> /etc/php.ini
echo "upload_max_filesize = 10M" >> /etc/php.ini
echo "post_max_size = 10M" >> /etc/php.ini

systemctl restart httpd
```

#### DigitalOcean Droplet

```bash
# Create new droplet with LAMP stack
# Connect via SSH

# Upload files
scp -r zoo-management/ root@your-server-ip:/var/www/html/

# Set permissions
chmod -R 755 /var/www/html/zoo-management
chmod 777 /var/www/html/zoo-management/backups
chmod 777 /var/www/html/zoo-management/exports

# Configure Apache virtual host
# (Use configuration from VPS section above)
```

#### Heroku

**Procfile:**
```
web: vendor/bin/heroku-php-apache2 public/
```

**composer.json:**
```json
{
    "require": {
        "php": "^7.4",
        "ext-mysqli": "*",
        "ext-pdo": "*",
        "ext-pdo_mysql": "*"
    }
}
```

## Database Setup

### MySQL Database Creation

```sql
-- Create production database
CREATE DATABASE zoo_management_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create production user
CREATE USER 'zoo_prod_user'@'localhost' IDENTIFIED BY 'secure_production_password';
GRANT ALL PRIVILEGES ON zoo_management_prod.* TO 'zoo_prod_user'@'localhost';
FLUSH PRIVILEGES;

-- Import schema
USE zoo_management_prod;
SOURCE database/setup_database.sql;

-- Create indexes for performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_bookings_user_id ON bookings(user_id);
CREATE INDEX idx_bookings_date ON bookings(visit_date);
CREATE INDEX idx_bookings_status ON bookings(status);
```

### Database Optimization

```sql
-- Enable query cache
SET GLOBAL query_cache_type = ON;
SET GLOBAL query_cache_size = 268435456; -- 256MB

-- Optimize tables
OPTIMIZE TABLE users;
OPTIMIZE TABLE bookings;
OPTIMIZE TABLE tickets;

-- Set up automated backups
-- Create backup script and add to cron
```

## SSL Certificate Setup

### Let's Encrypt (Free SSL)

```bash
# Install Certbot
sudo apt-get update
sudo apt-get install certbot python3-certbot-apache

# Obtain certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo crontab -e
# Add line:
0 12 * * * /usr/bin/certbot renew --quiet
```

### Commercial SSL

1. Purchase SSL certificate from CA
2. Generate CSR on server
3. Submit CSR to CA
4. Download and install certificate
5. Configure web server

## Performance Optimization

### PHP Configuration

**php.ini optimizations:**

```ini
; Memory and execution
memory_limit = 256M
max_execution_time = 30
max_input_time = 60

; File uploads
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20

; Session configuration
session.cookie_lifetime = 3600
session.cookie_secure = 1
session.cookie_httponly = 1
session.use_strict_mode = 1

; OPcache
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 2
```

### Database Optimization

```sql
-- Configure MySQL for production
[mysqld]
innodb_buffer_pool_size = 1G
query_cache_size = 256M
query_cache_type = 1
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

### Caching Implementation

**Redis Setup (Optional):**

```bash
# Install Redis
sudo apt-get install redis-server

# Configure PHP Redis extension
sudo apt-get install php-redis
```

**PHP Caching Example:**

```php
// Simple file-based caching
function cache_get($key) {
    $file = "cache/" . md5($key) . ".cache";
    if (file_exists($file) && (time() - filemtime($file)) < 3600) {
        return unserialize(file_get_contents($file));
    }
    return false;
}

function cache_set($key, $data) {
    $file = "cache/" . md5($key) . ".cache";
    file_put_contents($file, serialize($data));
}
```

## Monitoring and Maintenance

### Log Configuration

```php
// Error logging
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/zoo-management/php_errors.log');

// Custom application logging
function log_message($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$level] $message" . PHP_EOL;
    file_put_contents('/var/log/zoo-management/app.log', $log_entry, FILE_APPEND);
}
```

### Backup Strategy

**Automated Backup Script:**

```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/zoo-management"
DB_NAME="zoo_management_prod"
DB_USER="zoo_prod_user"
DB_PASS="secure_production_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/database_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/zoo-management --exclude='*.log'

# Remove old backups (keep 30 days)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

**Add to Crontab:**

```bash
# Daily backup at 2 AM
0 2 * * * /path/to/backup.sh >> /var/log/backup.log 2>&1
```

### Health Monitoring

**Simple Health Check Script:**

```php
<?php
// health-check.php
header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'checks' => []
];

// Database check
try {
    $pdo = new PDO($dsn, $username, $password);
    $health['checks']['database'] = 'healthy';
} catch (Exception $e) {
    $health['checks']['database'] = 'unhealthy';
    $health['status'] = 'unhealthy';
}

// Disk space check
$free_space = disk_free_space('/');
$total_space = disk_total_space('/');
$usage_percent = (1 - $free_space / $total_space) * 100;

if ($usage_percent > 90) {
    $health['checks']['disk_space'] = 'warning';
} else {
    $health['checks']['disk_space'] = 'healthy';
}

echo json_encode($health, JSON_PRETTY_PRINT);
?>
```

## Security Hardening

### File Permissions

```bash
# Set secure permissions
find /var/www/zoo-management -type d -exec chmod 755 {} \;
find /var/www/zoo-management -type f -exec chmod 644 {} \;

# Executable files
chmod +x /var/www/zoo-management/scripts/*.sh

# Writable directories
chmod 777 /var/www/zoo-management/backups
chmod 777 /var/www/zoo-management/exports

# Protect sensitive files
chmod 600 /var/www/zoo-management/.env
chmod 600 /var/www/zoo-management/config.production.php
```

### Server Security

```bash
# Update system
sudo apt-get update && sudo apt-get upgrade

# Configure firewall
sudo ufw enable
sudo ufw allow 22/tcp   # SSH
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS

# Disable unnecessary services
sudo systemctl disable bluetooth
sudo systemctl disable cups

# Install fail2ban
sudo apt-get install fail2ban
sudo systemctl enable fail2ban
```

### Application Security

```php
// CSRF Protection
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

// Rate limiting
function check_rate_limit($ip, $action) {
    $key = "rate_limit_{$action}_{$ip}";
    $attempts = $_SESSION[$key] ?? 0;
    
    if ($attempts > 5) {
        http_response_code(429);
        die('Rate limit exceeded');
    }
    
    $_SESSION[$key] = $attempts + 1;
}
```

## Troubleshooting

### Common Issues

**Database Connection Errors:**
```bash
# Check MySQL service
sudo systemctl status mysql

# Check MySQL logs
sudo tail -f /var/log/mysql/error.log

# Test connection
mysql -u zoo_prod_user -p zoo_management_prod
```

**Permission Issues:**
```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/zoo-management

# Check PHP error logs
sudo tail -f /var/log/apache2/error.log
```

**Performance Issues:**
```bash
# Check server resources
top
df -h
free -m

# Analyze slow queries
sudo tail -f /var/log/mysql/slow.log
```

### Emergency Procedures

**Site Maintenance Mode:**

```php
// maintenance.php
if (!isset($_GET['admin']) || $_GET['admin'] !== 'secret_key') {
    http_response_code(503);
    echo '<h1>Site Under Maintenance</h1>';
    echo '<p>We are currently performing scheduled maintenance.</p>';
    exit;
}
```

**Quick Rollback:**

```bash
# Restore from backup
cd /var/www
sudo mv zoo-management zoo-management-broken
sudo tar -xzf /backups/zoo-management/files_YYYYMMDD_HHMMSS.tar.gz
sudo chown -R www-data:www-data zoo-management
```

---

*Deployment Guide last updated: December 2024*
