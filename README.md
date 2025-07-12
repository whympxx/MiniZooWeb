![alt text](https://github.com/whympxx/MiniZooWeb/blob/main/TampilanDashboard.png?raw=true)
# 🦁 Zoo Management System

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Active-brightgreen.svg)]()

**Zoo Management System** adalah aplikasi web modern untuk manajemen kebun binatang yang komprehensif. Sistem ini menyediakan platform terintegrasi untuk pengelolaan tiket, pengunjung, dan administrasi kebun binatang dengan antarmuka yang user-friendly dan fitur-fitur canggih.

## 📋 Table of Contents

- [✨ Features](#-features)
- [🏗️ Architecture](#️-architecture)
- [🚀 Quick Start](#-quick-start)
- [📦 Installation](#-installation)
- [⚙️ Configuration](#️-configuration)
- [🔧 Usage](#-usage)
- [🔒 Security](#-security)
- [📊 API Documentation](#-api-documentation)
- [🧪 Testing](#-testing)
- [📖 Documentation](#-documentation)
- [🤝 Contributing](#-contributing)
- [📄 License](#-license)
- [🆘 Support](#-support)

## ✨ Features

### 👥 User Management
- **Multi-level Authentication**: User dan Admin dengan hak akses berbeda
- **Profile Management**: Pengelolaan profil pengguna yang lengkap
- **Session Management**: Sistem sesi yang aman dengan timeout otomatis
- **Password Security**: Enkripsi password dengan algoritma modern

### 🎫 Ticket Management
- **Online Booking**: Pemesanan tiket online yang mudah
- **Payment Integration**: Sistem pembayaran terintegrasi
- **E-Ticket Generation**: Generasi e-tiket dengan QR code
- **Booking History**: Riwayat pemesanan yang detail
- **Ticket Validation**: Validasi tiket real-time

### 🗺️ Zoo Features
- **Interactive Map**: Peta interaktif kebun binatang
- **Animal Information**: Database lengkap informasi hewan
- **Event Management**: Pengelolaan event dan pertunjukan
- **Facility Locator**: Pencarian fasilitas dan amenities

### 📊 Analytics & Reporting
- **Real-time Dashboard**: Dashboard analytics real-time
- **Revenue Reports**: Laporan pendapatan yang detail
- **Visitor Statistics**: Statistik pengunjung dan demografi
- **Export Functions**: Export data dalam berbagai format

### 🛡️ Security Features
- **XSS Protection**: Perlindungan dari Cross-Site Scripting
- **CSRF Protection**: Perlindungan dari Cross-Site Request Forgery
- **SQL Injection Prevention**: Prepared statements untuk keamanan database
- **Access Control**: Kontrol akses berbasis role

### 🔧 Admin Panel
- **Comprehensive Dashboard**: Dashboard admin yang lengkap
- **User Management**: Pengelolaan pengguna dan permissions
- **Content Management**: Pengelolaan konten dan informasi
- **System Settings**: Konfigurasi sistem yang fleksibel
- **Backup & Restore**: Sistem backup otomatis

## 🏗️ Architecture

### Technology Stack

```
┌─────────────────┐
│   Frontend      │
├─────────────────┤
│ HTML5/CSS3      │
│ Tailwind CSS    │
│ JavaScript      │
│ Responsive UI   │
└─────────────────┘
          │
┌─────────────────┐
│   Backend       │
├─────────────────┤
│ PHP 7.4+        │
│ MVC Pattern     │
│ Session Mgmt    │
│ Security Layer  │
└─────────────────┘
          │
┌─────────────────┐
│   Database      │
├─────────────────┤
│ MySQL 5.7+      │
│ Normalized DB   │
│ Indexes         │
│ Stored Proc     │
└─────────────────┘
```

### Directory Structure

```
Tugas13/
├── 📄 index.php                    # Application entry point
├── 📄 config.php                   # System configuration
├── 📄 .htaccess                    # Apache configuration
├── 📄 README.md                    # Main documentation
├── 📄 CHANGELOG.md                 # Version history
├── 📄 LICENSE                      # License information
│
├── 📁 assets/                      # Static assets
│   ├── 📁 css/                    # Stylesheets (16 files)
│   │   ├── admin-*.css            # Admin panel styles
│   │   ├── dashboard-*.css        # Dashboard styles
│   │   └── *.css                  # Page-specific styles
│   ├── 📁 images/                 # Images and media (8 files)
│   │   ├── animal-photos/         # Animal photographs
│   │   └── ui-assets/             # UI icons and graphics
│   └── 📁 js/                     # JavaScript files
│       ├── main.js                # Core functionality
│       ├── admin.js               # Admin panel scripts
│       └── vendor/                # Third-party libraries
│
├── 📁 pages/                      # User-facing pages
│   ├── 📄 Login.php               # User authentication
│   ├── 📄 Register.php            # User registration
│   ├── 📄 home.php                # Homepage
│   ├── 📄 dashboard.php           # User dashboard
│   ├── 📄 peta.php                # Zoo map
│   ├── 📄 profil.php              # User profile
│   ├── 📄 statistik.php           # Public statistics
│   ├── 📄 tiket.php               # Ticket booking
│   ├── 📄 tiket_bayar.php         # Payment processing
│   ├── 📄 tiket_export.php        # Ticket export
│   ├── 📄 tiket_konfirmasi.php    # Booking confirmation
│   ├── 📄 tiket_riwayat.php       # Booking history
│   └── 📄 logout.php              # Session termination
│
├── 📁 admin/                      # Administration panel
│   ├── 📄 admin_dashboard.php     # Admin dashboard
│   ├── 📄 admin_management.php    # User management
│   ├── 📄 admin_settings.php      # System settings
│   ├── 📄 admin_analytics.php     # Analytics & reports
│   ├── 📄 admin_actions.php       # Bulk actions
│   ├── 📄 admin_bulk_actions.php  # Batch operations
│   ├── 📄 admin_settings_actions.php # Settings handlers
│   ├── 📄 admin_tiket_management.php # Ticket management
│   ├── 📄 admin_tiket_bulk_actions.php # Ticket bulk ops
│   └── 📄 create_admin.php        # Admin creation utility
│
├── 📁 includes/                   # Core system files
│   ├── 📄 db.php                  # Database connection
│   ├── 📄 notification_system.php # Notification handler
│   ├── 📄 auto_confirm_orders.php # Order automation
│   ├── 📄 database_backup.php     # Backup utilities
│   ├── 📄 download_file.php       # File download handler
│   ├── 📄 get_order_details.php   # Order processing
│   └── 📄 test_login.php          # Authentication testing
│
├── 📁 database/                   # Database schemas
│   ├── 📄 auth_system.sql         # Authentication tables
│   ├── 📄 setup_database.sql      # Database initialization
│   └── 📄 sample_orders.sql       # Sample data
│
├── 📁 docs/                       # Additional documentation
│   ├── 📄 API.md                  # API documentation
│   ├── 📄 DEPLOYMENT.md           # Deployment guide
│   ├── 📄 CONTRIBUTING.md         # Contribution guidelines
│   └── 📄 SECURITY.md             # Security policies
│
├── 📁 tests/                      # Test suites
│   ├── 📁 unit/                   # Unit tests
│   ├── 📁 integration/            # Integration tests
│   └── 📄 phpunit.xml             # Test configuration
│
├── 📁 backups/                    # System backups
│   ├── 📄 .htaccess               # Access protection
│   └── auto-generated backups
│
└── 📁 exports/                    # Generated exports
    ├── 📄 .htaccess               # Access protection
    └── user-generated exports
```

## 🚀 Quick Start

### Prerequisites

- **XAMPP/WAMP/LAMP** with PHP 7.4+
- **MySQL** 5.7+ or **MariaDB** 10.3+
- **Web Browser** (Chrome, Firefox, Safari, Edge)
- **Text Editor/IDE** (VS Code, PHPStorm, Sublime Text)

### 🏃‍♂️ Get Running in 5 Minutes

```bash
# 1. Clone/Download project
git clone https://github.com/your-repo/zoo-management.git
# OR download and extract to htdocs/Tugas13

# 2. Start XAMPP services
# Start Apache and MySQL from XAMPP Control Panel

# 3. Create database
mysql -u root -p
CREATE DATABASE zoo_management;

# 4. Import database
mysql -u root -p zoo_management < database/setup_database.sql

# 5. Configure database connection
# Edit includes/db.php or config.php

# 6. Access application
# Open browser: http://localhost/Tugas13
```

## 📦 Installation

### Detailed Installation Steps

#### 1. Environment Setup

```bash
# Download and install XAMPP
# https://www.apachefriends.org/download.html

# Start XAMPP Control Panel
# Enable Apache and MySQL modules
```

#### 2. Project Setup

```bash
# Navigate to XAMPP htdocs directory
cd C:\xampp\htdocs

# Clone or extract project
# Ensure project is in: C:\xampp\htdocs\Tugas13
```

#### 3. Database Configuration

```sql
-- Create database
CREATE DATABASE zoo_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (optional)
CREATE USER 'zoo_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON zoo_management.* TO 'zoo_user'@'localhost';
FLUSH PRIVILEGES;

-- Import schema
USE zoo_management;
SOURCE database/setup_database.sql;
SOURCE database/sample_orders.sql;
```

#### 4. Application Configuration

```php
// config.php - Update database settings
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'zoo_user');
define('DB_PASSWORD', 'secure_password');
define('DB_NAME', 'zoo_management');

// Update application URL
define('APP_URL', 'http://localhost/Tugas13');
```

#### 5. File Permissions (Linux/Mac)

```bash
# Set proper permissions
chmod 755 -R Tugas13/
chmod 777 -R Tugas13/backups/
chmod 777 -R Tugas13/exports/
```

## ⚙️ Configuration

### Environment Variables

Create `.env` file for environment-specific settings:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=zoo_management
DB_USERNAME=root
DB_PASSWORD=

# Application Settings
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/Tugas13

# Email Configuration
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password

# Security Settings
SESSION_TIMEOUT=3600
MAX_LOGIN_ATTEMPTS=5
```

### System Settings

Access admin panel at `/admin/admin_settings.php` to configure:

- **General Settings**: Site name, description, contact info
- **Ticket Pricing**: Price tiers and categories
- **Payment Settings**: Payment gateways and methods
- **Email Templates**: Notification email templates
- **Security Settings**: Password policies, session settings

## 🔧 Usage

### For Visitors

1. **Registration**: Create account at `/pages/Register.php`
2. **Login**: Authenticate at `/pages/Login.php`
3. **Browse**: Explore zoo information and map
4. **Book Tickets**: Purchase tickets online
5. **Manage Profile**: Update personal information
6. **View History**: Check booking history

### For Administrators

1. **Access Admin Panel**: Login with admin credentials
2. **Dashboard**: Monitor system statistics
3. **User Management**: Manage user accounts
4. **Ticket Management**: Handle bookings and sales
5. **Content Management**: Update zoo information
6. **System Maintenance**: Backup, settings, logs

### API Endpoints

```php
// Authentication
POST /api/auth/login
POST /api/auth/register
POST /api/auth/logout

// Tickets
GET  /api/tickets
POST /api/tickets
GET  /api/tickets/{id}
PUT  /api/tickets/{id}

// Users
GET  /api/users/profile
PUT  /api/users/profile
GET  /api/users/bookings

// Admin
GET  /api/admin/stats
GET  /api/admin/users
GET  /api/admin/reports
```

## 🔒 Security

### Security Measures Implemented

- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Input sanitization and output encoding
- **CSRF Protection**: Token-based request validation
- **Session Security**: Secure session configuration
- **Password Security**: Bcrypt hashing
- **Access Control**: Role-based permissions
- **File Upload Security**: Type and size validation
- **Error Handling**: Secure error messages

### Security Best Practices

```php
// Input Validation
$input = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING);

// Prepared Statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// Password Hashing
$hash = password_hash($password, PASSWORD_DEFAULT);

// CSRF Protection
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('CSRF token mismatch');
}
```

## 📊 API Documentation

Comprehensive API documentation available at [`docs/API.md`](docs/API.md)

### Authentication

All API requests require authentication via session or API key:

```php
// Session-based authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// API key authentication
$api_key = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (!validate_api_key($api_key)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid API key']);
    exit;
}
```

## 🧪 Testing

### Running Tests

```bash
# Install PHPUnit (if not installed)
composer install

# Run all tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite unit
vendor/bin/phpunit --testsuite integration

# Generate coverage report
vendor/bin/phpunit --coverage-html coverage/
```

### Test Structure

```
tests/
├── unit/
│   ├── AuthTest.php
│   ├── TicketTest.php
│   └── UserTest.php
├── integration/
│   ├── DatabaseTest.php
│   └── ApiTest.php
└── fixtures/
    └── sample_data.sql
```

## 📖 Documentation

- **[Installation Guide](docs/INSTALLATION.md)** - Detailed setup instructions
- **[API Documentation](docs/API.md)** - Complete API reference
- **[Database Schema](docs/DATABASE.md)** - Database structure and relations
- **[Security Guide](docs/SECURITY.md)** - Security implementation details
- **[Deployment Guide](docs/DEPLOYMENT.md)** - Production deployment
- **[Contributing Guide](docs/CONTRIBUTING.md)** - How to contribute
- **[Changelog](CHANGELOG.md)** - Version history and updates

## 🤝 Contributing

We welcome contributions! Please read our [Contributing Guide](docs/CONTRIBUTING.md) for details.

### Development Workflow

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

### Code Standards

- Follow PSR-12 coding standards
- Write unit tests for new features
- Update documentation
- Use meaningful commit messages

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

### Getting Help

- **Documentation**: Check our comprehensive docs
- **Issues**: Report bugs via GitHub Issues
- **Discussions**: Join community discussions
- **Email**: contact@zoo-management.com

### Troubleshooting

#### Common Issues

**Database Connection Error**
```
- Check database credentials in config.php
- Ensure MySQL service is running
- Verify database exists and is accessible
```

**Permission Denied**
```
- Check file permissions (755 for directories, 644 for files)
- Ensure Apache has read access to project directory
- Check SELinux settings (Linux)
```

**Session Issues**
```
- Verify session configuration in php.ini
- Check session directory permissions
- Clear browser cookies and cache
```

### System Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| PHP | 7.4 | 8.0+ |
| MySQL | 5.7 | 8.0+ |
| Apache | 2.4 | 2.4+ |
| Memory | 128MB | 256MB+ |
| Storage | 100MB | 500MB+ |

---

**Made with ❤️ for wildlife conservation and digital innovation**

*Last updated: December 2024*
