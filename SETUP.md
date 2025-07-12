# 🚀 Quick Setup Guide

## Prerequisites

1. **XAMPP** installed and running
   - Apache server running
   - MySQL server running

## Automatic Installation

### Option 1: Auto Installer (Recommended)

1. Open terminal/command prompt in project directory
2. Run the auto installer:
   ```bash
   php install.php
   ```
3. Follow the installation steps
4. Access your application at `http://localhost/Tugas13`

### Option 2: Manual Installation

1. **Create Database**
   ```sql
   CREATE DATABASE zoo_management;
   ```

2. **Import Database Schema**
   - Import `database/setup_database.sql` via phpMyAdmin
   - Or run: `mysql -u root zoo_management < database/setup_database.sql`

3. **Configure Database**
   - Edit `config.php` if needed
   - Default settings work with standard XAMPP

4. **Test Connection**
   ```bash
   php test_database.php
   ```

## Default Login Credentials

- **Admin Account:**
  - Username: `admin`
  - Email: `admin@zoo-management.local`
  - Password: `admin123`

- **Test User Account:**
  - Username: `user1`
  - Email: `user1@example.com`
  - Password: `password`

⚠️ **Security Note:** Change default passwords after first login!

## Verification Steps

1. ✅ Visit `http://localhost/Tugas13`
2. ✅ Login with admin credentials
3. ✅ Check admin dashboard functionality
4. ✅ Test user registration
5. ✅ Test ticket booking system

## File Structure Check

Ensure your file structure looks like this:

```
Tugas13/
├── 📄 index.php          # ✅ Entry point
├── 📄 config.php         # ✅ Configuration
├── 📁 assets/            # ✅ CSS, JS, Images
├── 📁 pages/             # ✅ User pages (13 files)
├── 📁 admin/             # ✅ Admin panel (10 files)
├── 📁 includes/          # ✅ Core files (7 files)
├── 📁 database/          # ✅ SQL files (3 files)
├── 📁 docs/              # ✅ Documentation (4 files)
├── 📁 backups/           # ✅ Backup directory
└── 📁 exports/           # ✅ Export directory
```

## Troubleshooting

### Database Connection Issues

1. **Check XAMPP Services**
   ```bash
   # Ensure MySQL is running in XAMPP Control Panel
   ```

2. **Verify Database Exists**
   ```sql
   SHOW DATABASES LIKE 'zoo_management';
   ```

3. **Test Connection**
   ```bash
   php test_database.php
   ```

### Common Errors

**Error: "Database connection failed"**
- ✅ Start XAMPP MySQL service
- ✅ Check credentials in `config.php`
- ✅ Ensure database exists

**Error: "Table doesn't exist"**
- ✅ Import `database/setup_database.sql`
- ✅ Check database name in config

**Error: "Permission denied"**
- ✅ Set folder permissions (777 for backups/exports)
- ✅ Check Apache document root

### File Permissions (Linux/Mac)

```bash
chmod 755 -R Tugas13/
chmod 777 Tugas13/backups/
chmod 777 Tugas13/exports/
```

## Quick Commands

```bash
# Test all PHP files for syntax errors
find . -name "*.php" -exec php -l {} \;

# Check database connection
php test_database.php

# Run auto installer
php install.php

# View logs (if enabled)
tail -f logs/error.log
```

## Next Steps After Setup

1. 🔐 **Change default passwords**
2. ⚙️ **Configure system settings** in admin panel
3. 🎨 **Customize appearance** via CSS files
4. 📊 **Add sample data** for testing
5. 🔒 **Review security settings**

## Support

- 📚 **Full Documentation:** `README.md`
- 🔧 **API Guide:** `docs/API.md`
- 🚀 **Deployment:** `docs/DEPLOYMENT.md`
- 🔒 **Security:** `docs/SECURITY.md`

---

**⚡ Quick Start:** Run `php install.php` and you're ready to go!
