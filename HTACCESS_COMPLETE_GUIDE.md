# Complete .htaccess Setup Guide

## ✅ The Solution

Laravel **requires** a `.env` file to exist, but we can make it minimal with empty values, and Laravel will use the `SetEnv` values from `.htaccess` instead!

---

## 📁 Required Files

### 1. **Minimal .env File** (Required!)

Create: `public_html/tarek/.env`

```env
# Minimal .env - all values come from .htaccess SetEnv
# DO NOT add values here - they are in .htaccess!

APP_NAME=
APP_ENV=
APP_KEY=
APP_DEBUG=
APP_URL=

DB_CONNECTION=
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

SESSION_DRIVER=
SESSION_LIFETIME=
QUEUE_CONNECTION=
CACHE_DRIVER=

MAIL_MAILER=
MAIL_HOST=
MAIL_PORT=
MAIL_ENCRYPTION=

IMAP_HOST=
IMAP_PORT=
IMAP_ENCRYPTION=
```

**Why?** Laravel's bootstrap checks if `.env` exists. Empty values tell Laravel to use server environment variables from `.htaccess`!

---

### 2. **Root .htaccess** (Already created)

File: `public_html/tarek/.htaccess`

Already has all `SetEnv` configuration! ✓

---

### 3. **Public .htaccess** (Already created)

File: `public_html/tarek/public/.htaccess`

Already has all `SetEnv` configuration! ✓

---

## 🚀 Complete Setup Steps

### Step 1: Create Minimal .env

```bash
cd ~/public_html/tarek
```

Create `.env` file with the empty values shown above.

Or upload the `.env.minimal` file I created and rename it to `.env`

### Step 2: Verify .htaccess Files

Both files should have:

```apache
<IfModule mod_env.c>
    SetEnv APP_NAME "PrintItMat"
    SetEnv APP_KEY base64:8dQ7xw9W8CbKk5UlZjKMfGzKWz0XvP8qN5YzLtQmRkE=
    SetEnv APP_URL https://tarek.arvixi.net
    SetEnv DB_HOST localhost
    SetEnv DB_DATABASE u624844894_tarek
    SetEnv DB_USERNAME u624844894_tarek
    SetEnv DB_PASSWORD Gofuckurself123*
    # ... etc
</IfModule>
```

### Step 3: Install Composer Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

Or upload `vendor` folder via FTP

### Step 4: Fix Permissions

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### Step 5: Create Required Directories

```bash
mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p storage/logs
mkdir -p bootstrap/cache
```

### Step 6: Import Database

1. phpMyAdmin → Select `u624844894_tarek`
2. Import → Choose `database_import.sql`
3. Click Go

### Step 7: Test

Visit: https://tarek.arvixi.net

---

## 🔍 How It Works

```
1. Laravel boots and looks for .env file ✓
2. Finds .env with empty values ✓
3. For each empty value, checks server environment ✓
4. Reads SetEnv values from .htaccess ✓
5. Uses those values for configuration ✓
```

**Result:** All configuration from `.htaccess`, no `.env` values needed!

---

## 📋 Complete File Structure

```
public_html/tarek/
├── .env                    ← Minimal (empty values) - REQUIRED!
├── .htaccess              ← Has all SetEnv values
├── app/
├── bootstrap/
│   └── cache/             ← 755 permissions
├── config/
├── database/
├── public/
│   ├── .htaccess          ← Has all SetEnv values
│   └── index.php
├── resources/
├── routes/
├── storage/               ← 755 permissions
│   ├── framework/
│   │   ├── sessions/
│   │   ├── views/
│   │   └── cache/data/
│   └── logs/
├── vendor/                ← Run composer install
└── composer.json
```

---

## ✅ Verification Checklist

Run through this:

- [ ] `.env` file exists with empty values
- [ ] `.htaccess` has all `SetEnv` values
- [ ] `public/.htaccess` has all `SetEnv` values
- [ ] `vendor/` folder exists
- [ ] `storage/` has 755 permissions
- [ ] `bootstrap/cache/` has 755 permissions
- [ ] Required storage directories exist
- [ ] Database imported (has tables)
- [ ] Subdomain points to `public_html/tarek/public`
- [ ] PHP version is 8.2+

---

## 🧪 Test Your Setup

I created a new diagnostic file: `public/check-system.php`

**Upload to:** `public_html/tarek/public/check-system.php`

**Visit:** https://tarek.arvixi.net/check-system.php

This will check:
- ✅ If .env exists
- ✅ If environment variables are set from .htaccess
- ✅ If vendor folder exists
- ✅ If database connection works
- ✅ If required tables exist
- ✅ If permissions are correct

**DELETE after testing!**

---

## 🎯 Priority Values in .htaccess

Make sure these are set:

```apache
SetEnv APP_KEY base64:8dQ7xw9W8CbKk5UlZjKMfGzKWz0XvP8qN5YzLtQmRkE=
SetEnv APP_URL https://tarek.arvixi.net
SetEnv APP_DEBUG true
SetEnv APP_ENV production

SetEnv DB_HOST localhost
SetEnv DB_DATABASE u624844894_tarek
SetEnv DB_USERNAME u624844894_tarek
SetEnv DB_PASSWORD Gofuckurself123*

SetEnv SESSION_DRIVER database
SetEnv CACHE_DRIVER file
SetEnv QUEUE_CONNECTION sync
```

---

## 🔧 Debugging

If still showing blank page:

1. **Enable debug in .htaccess:**
   ```apache
   SetEnv APP_DEBUG true
   ```

2. **Add error display at top of `public/.htaccess`:**
   ```apache
   php_flag display_errors on
   php_value error_reporting E_ALL
   ```

3. **Check Laravel logs:**
   ```
   storage/logs/laravel.log
   ```

4. **Visit diagnostic:**
   ```
   https://tarek.arvixi.net/check-system.php
   ```

---

## ⚠️ Common Issues

### "White blank page"
→ Missing vendor folder
→ Run: `composer install`

### "No application encryption key"
→ APP_KEY not set in .htaccess
→ Check SetEnv APP_KEY line exists

### "Database connection failed"
→ Wrong credentials in .htaccess
→ OR database not created in cPanel

### "Permission denied"
→ Storage not writable
→ Run: `chmod -R 755 storage bootstrap/cache`

### ".env file not found"
→ Create minimal .env with empty values
→ Use the .env.minimal template

---

## 📦 Files I Created for You

1. **.env.minimal** - Upload as `.env` to `public_html/tarek/.env`
2. **public/check-system.php** - Complete diagnostic tool
3. **COMPLETE_HTACCESS_SETUP.txt** - Quick reference
4. **THIS FILE** - Complete guide

---

## 🎉 After Everything Works

1. Change in .htaccess:
   ```apache
   SetEnv APP_DEBUG false
   SetEnv APP_ENV production
   ```

2. Remove error display lines from .htaccess

3. Delete test files:
   - public/check-system.php
   - public/phpinfo.php
   - public/check-errors.php
   - public/test-env.php

---

**Upload the minimal .env file and run check-system.php!**

Visit: https://tarek.arvixi.net/check-system.php

