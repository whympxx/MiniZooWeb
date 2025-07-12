# 📦 Installation Guide

## Prerequisites

### System Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| PHP | 7.4 | 8.0+ |
| MySQL | 5.7 | 8.0+ |
| Apache | 2.4 | 2.4+ |
| Memory | 128MB | 256MB+ |
| Storage | 100MB | 500MB+ |

### Required PHP Extensions

```bash
# Check installed extensions
php -m

# Required extensions:
- mysqli
- pdo_mysql
- session
- json
- mbstring
- openssl
- curl
- gd (for image processing)
```

## Installation Methods

### Method 1: XAMPP (Recommended for Development)

1. **Download XAMPP**
   ```bash
   # Download from: https://www.apachefriends.org/download.html
   # Choose appropriate version for your OS
   ```

2. **Install XAMPP**
   - Run installer as administrator
   - Select Apache, MySQL, and PHP
   - Install to default directory (C:\xampp)

3. **Start Services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL modules
   - Verify services are running (green status)

4. **Clone Project**
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/whympxx/MiniZooWeb.git ZooWeb
   ```

### Method 2: Manual LAMP Stack

#### Ubuntu/Debian

```bash
# Update package index
sudo apt update

# Install Apache
sudo apt install apache2

# Install MySQL
sudo apt install mysql-server

# Install PHP and extensions
sudo apt install php php-mysql php-mysqli php-mbstring php-json php-curl php-gd

# Enable Apache modules
sudo a2enmod rewrite
sudo systemctl restart apache2

# Clone project
cd /var/www/html
sudo git clone https://github.com/whympxx/MiniZooWeb.git ZooWeb
sudo chown -R www-data:www-data ZooWeb
```

#### CentOS/RHEL

```bash
# Install Apache
sudo yum install httpd

# Install MySQL
sudo yum install mysql-server

# Install PHP
sudo yum install php php-mysql php-mbstring php-json php-curl php-gd

# Start services
sudo systemctl start httpd
sudo systemctl start mysqld
sudo systemctl enable httpd
sudo systemctl enable mysqld

# Clone project
cd /var/www/html
sudo git clone https://github.com/whympxx/MiniZooWeb.git ZooWeb
```

## Database Setup

### 1. Create Database

```sql
-- Connect to MySQL
mysql -u root -p

-- Create database
CREATE DATABASE zoo_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (optional but recommended)
CREATE USER 'zoo_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON zoo_management.* TO 'zoo_user'@'localhost';
FLUSH PRIVILEGES;

-- Exit MySQL
EXIT;
```

### 2. Import Database Schema

```bash
# Method 1: Using mysql command
mysql -u zoo_user -p zoo_management < database/setup_database.sql

# Method 2: Using phpMyAdmin
# 1. Open http://localhost/phpmyadmin
# 2. Select zoo_management database
# 3. Click Import tab
# 4. Choose database/setup_database.sql
# 5. Click Go
```

### 3. Import Sample Data (Optional)

```bash
# Import sample orders
mysql -u zoo_user -p zoo_management < database/sample_orders.sql

# Import authentication system
mysql -u zoo_user -p zoo_management < database/auth_system.sql
```

## Configuration

### 1. Database Configuration

Edit `config.php` or `includes/db.php`:

```php
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'zoo_user');
define('DB_PASSWORD', 'secure_password_here');
define('DB_NAME', 'zoo_management');
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_URL', 'http://localhost/ZooWeb');
define('APP_NAME', 'Zoo Management System');
define('APP_VERSION', '1.0.0');

// Security settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('CSRF_TOKEN_EXPIRE', 1800); // 30 minutes

// Email configuration (optional)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');
define('SMTP_FROM_EMAIL', 'noreply@zoomanagement.com');
define('SMTP_FROM_NAME', 'Zoo Management System');
?>
```

### 2. Environment Variables (.env)

Create `.env` file in root directory:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=zoo_management
DB_USERNAME=zoo_user
DB_PASSWORD=secure_password_here

# Application Settings
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/ZooWeb
APP_NAME="Zoo Management System"

# Security Settings
SESSION_LIFETIME=3600
MAX_LOGIN_ATTEMPTS=5
CSRF_TOKEN_EXPIRE=1800

# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
SMTP_FROM_EMAIL=noreply@zoomanagement.com
SMTP_FROM_NAME="Zoo Management System"

# File Upload Settings
MAX_FILE_SIZE=10485760
ALLOWED_FILE_TYPES=jpg,jpeg,png,gif,pdf,doc,docx
UPLOAD_PATH=uploads/

# Cache Settings
CACHE_ENABLED=true
CACHE_LIFETIME=3600
```

### 3. Apache Configuration

#### .htaccess Configuration

The project includes `.htaccess` file with:

```apache
# Enable URL rewriting
RewriteEngine On

# Deny access to sensitive files
<Files "*.sql">
    Order deny,allow
    Deny from all
</Files>

<Files "*.log">
    Order deny,allow
    Deny from all
</Files>

<Files ".env">
    Order deny,allow
    Deny from all
</Files>

# Security headers
Header always set X-Frame-Options DENY
Header always set X-Content-Type-Options nosniff
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"

# Compress files
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Cache static files
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### 4. File Permissions

#### Linux/Mac

```bash
# Set proper permissions
chmod 755 -R ZooWeb/
chmod 777 -R ZooWeb/backups/
chmod 777 -R ZooWeb/exports/
chmod 777 -R ZooWeb/uploads/ # if exists
chmod 600 ZooWeb/.env
chmod 600 ZooWeb/config.php
```

#### Windows

```powershell
# Using icacls command
icacls "C:\xampp\htdocs\ZooWeb" /grant "Everyone:(OI)(CI)F"
icacls "C:\xampp\htdocs\ZooWeb\backups" /grant "Everyone:(OI)(CI)F"
icacls "C:\xampp\htdocs\ZooWeb\exports" /grant "Everyone:(OI)(CI)F"
```

## Admin Account Setup

### Method 1: Using Installation Script

```bash
# Access installation script
http://localhost/ZooWeb/install.php

# Follow on-screen instructions to:
# 1. Test database connection
# 2. Create admin account
# 3. Set initial configuration
```

### Method 2: Manual Admin Creation

```bash
# Access admin creation script
http://localhost/ZooWeb/admin/create_admin.php

# Or run SQL manually:
```

```sql
-- Create admin user
INSERT INTO users (username, email, password, role, created_at) VALUES
('admin', 'admin@zoomanagement.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW());

-- Default password is 'password' (change immediately!)
```

## Verification

### 1. Test Database Connection

```bash
# Access test script
http://localhost/ZooWeb/test_database_connection.php

# Should show: "Database connection successful"
```

### 2. Test Application

```bash
# Access application
http://localhost/ZooWeb

# Should show homepage with navigation
```

### 3. Test Admin Panel

```bash
# Access admin panel
http://localhost/ZooWeb/admin/admin_dashboard.php

# Login with admin credentials
```

## Troubleshooting

### Common Issues

#### Database Connection Error

```php
// Error: "Connection failed: Access denied for user"
// Solution: Check credentials in config.php
// Verify MySQL service is running
// Check user permissions in MySQL
```

#### Permission Denied

```bash
# Error: "Permission denied"
# Solution: Set proper file permissions
chmod 755 -R ZooWeb/
chown -R www-data:www-data ZooWeb/ # Linux
```

#### Session Not Working

```php
// Error: "Session not starting"
// Solution: Check PHP session configuration
// Verify session directory exists and is writable
// Check session.save_path in php.ini
```

#### .htaccess Not Working

```apache
# Error: "Internal Server Error"
# Solution: Enable mod_rewrite in Apache
# Check AllowOverride directive
# Verify .htaccess syntax
```

### Debug Mode

Enable debug mode in config.php:

```php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable debug mode
define('APP_DEBUG', true);
```

### Log Files

Check these log files for errors:

```bash
# Apache error log
tail -f /var/log/apache2/error.log

# MySQL error log
tail -f /var/log/mysql/error.log

# PHP error log
tail -f /var/log/php_errors.log

# Application logs
tail -f ZooWeb/logs/application.log
```

## Post-Installation

### 1. Security Checklist

- [ ] Change default admin password
- [ ] Update database credentials
- [ ] Set strong session configuration
- [ ] Enable HTTPS in production
- [ ] Configure firewall rules
- [ ] Set up regular backups
- [ ] Update file permissions
- [ ] Configure email settings

### 2. Performance Optimization

- [ ] Enable PHP OPcache
- [ ] Configure MySQL query cache
- [ ] Set up CDN for static assets
- [ ] Enable gzip compression
- [ ] Configure browser caching
- [ ] Optimize database indexes

### 3. Monitoring Setup

- [ ] Set up log rotation
- [ ] Configure monitoring tools
- [ ] Set up alerts for errors
- [ ] Monitor disk space
- [ ] Track performance metrics

## Next Steps

1. **Configuration**: Complete system configuration
2. **Testing**: Run test suite to verify installation
3. **Content**: Add initial content and data
4. **Security**: Review and enhance security settings
5. **Backup**: Set up automated backup system
6. **Documentation**: Review user documentation

## Support

For installation issues:

1. Check troubleshooting section
2. Review log files for errors
3. Consult documentation
4. Contact support team
5. Report bugs on GitHub

---

*Installation guide last updated: July 2025*
