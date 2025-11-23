# Debug 500 Internal Server Error

## 🔍 Step 1: Test Environment Variables

I created a test file for you: `public/test-env.php`

**Visit:** https://tarek.arvixi.net/test-env.php

This will show:
- ✅ If environment variables are working
- ✅ PHP version
- ✅ If vendor folder exists
- ✅ If storage folder has correct permissions
- ✅ All environment variables

**After checking, DELETE this file for security!**

---

## 🔧 Step 2: Enable Laravel Debug Mode

In **BOTH** .htaccess files, change this line:

```apache
SetEnv APP_DEBUG false
```

To:

```apache
SetEnv APP_DEBUG true
```

**Files to update:**
1. `public_html/tarek/.htaccess`
2. `public_html/tarek/public/.htaccess`

Then visit: https://tarek.arvixi.net

You'll see the actual error message!

**After fixing, change back to `false`**

---

## ✅ Step 3: Common 500 Error Fixes

### Fix 1: Missing Composer Dependencies

**Problem:** vendor folder doesn't exist

**Solution via Terminal:**
```bash
cd ~/public_html/tarek
composer install --no-dev --optimize-autoloader
```

**Solution without Terminal:**
- Install composer on your local computer
- Run `composer install --no-dev`
- Upload entire `vendor` folder via FTP

---

### Fix 2: Storage Permissions

**Problem:** Laravel can't write to storage folder

**Solution via Terminal:**
```bash
cd ~/public_html/tarek
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

**Solution via File Manager:**
1. Right-click `storage` folder
2. Change Permissions → 755
3. ✅ Check "Recurse into subdirectories"
4. Click "Change Permissions"
5. Repeat for `bootstrap/cache`

---

### Fix 3: Missing Storage Directories

**Problem:** Required directories missing in storage

**Solution via Terminal:**
```bash
cd ~/public_html/tarek
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R 755 storage bootstrap/cache
```

**Solution via File Manager:**
Create these folders in `storage/framework/`:
- `sessions/`
- `views/`
- `cache/`
- `cache/data/`

And in root:
- `storage/logs/`
- `bootstrap/cache/`

---

### Fix 4: mod_env Not Enabled

**Problem:** SetEnv doesn't work (environment variables not set)

**Test:** Visit https://tarek.arvixi.net/test-env.php
- If APP_KEY shows "NOT SET", mod_env is disabled

**Solution 1:** Contact Hostinger support to enable mod_env

**Solution 2:** Use .env file instead:

Create `public_html/tarek/.env` file:
```env
APP_NAME="PrintItMat"
APP_ENV=production
APP_KEY=base64:8dQ7xw9W8CbKk5UlZjKMfGzKWz0XvP8qN5YzLtQmRkE=
APP_DEBUG=false
APP_URL=https://tarek.arvixi.net

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u624844894_tarek
DB_USERNAME=u624844894_tarek
DB_PASSWORD=Gofuckurself123*

SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync
CACHE_DRIVER=file
```

---

### Fix 5: PHP Version Too Old

**Problem:** PHP version below 8.2

**Solution:**
1. cPanel → **Select PHP Version**
2. Choose **8.2** or higher
3. Enable required extensions:
   - ✅ mbstring
   - ✅ mysqli
   - ✅ pdo_mysql
   - ✅ openssl
   - ✅ tokenizer
   - ✅ json
   - ✅ curl

---

### Fix 6: Database Not Imported

**Problem:** Database tables don't exist

**Solution:**
1. cPanel → phpMyAdmin
2. Select database: `u624844894_tarek`
3. Check if tables exist (should see users, customers, etc.)
4. If empty, Import → `database_import.sql`

---

### Fix 7: Wrong Document Root

**Problem:** Subdomain not pointing to public folder

**Check:** Does your URL show `/public/` in it?

**Solution:**
1. cPanel → Subdomains
2. Find: tarek.arvixi.net
3. Change Document Root to: `public_html/tarek/public`
4. Save

---

## 📋 Quick Diagnostic Checklist

Run through this checklist:

```bash
cd ~/public_html/tarek

# Check if files exist
[ -d "vendor" ] && echo "✓ vendor exists" || echo "✗ vendor MISSING"
[ -d "storage" ] && echo "✓ storage exists" || echo "✗ storage MISSING"
[ -d "bootstrap/cache" ] && echo "✓ bootstrap/cache exists" || echo "✗ bootstrap/cache MISSING"
[ -f ".env" ] && echo "✓ .env exists" || echo "✗ .env missing (using .htaccess)"
[ -f "public/index.php" ] && echo "✓ index.php exists" || echo "✗ index.php MISSING"

# Check permissions
ls -la storage | grep "drwx"
ls -la bootstrap/cache | grep "drwx"

# Check PHP version
php -v
```

---

## 🆘 Most Likely Causes

Based on your setup, the 500 error is most likely:

1. **Missing vendor folder** (70% probability)
   → Run `composer install`

2. **Wrong storage permissions** (20% probability)
   → Run `chmod -R 755 storage bootstrap/cache`

3. **mod_env not enabled** (10% probability)
   → Create .env file instead

---

## 📊 Step-by-Step Debug Process

### Step 1: Visit Test File
```
https://tarek.arvixi.net/test-env.php
```

This will tell you EXACTLY what's wrong!

### Step 2: Enable APP_DEBUG
Change `APP_DEBUG false` to `APP_DEBUG true` in .htaccess

### Step 3: Read the Error
Visit: https://tarek.arvixi.net

The error message will tell you exactly what to fix!

### Step 4: Common Errors & Solutions

**"Class 'Illuminate\Foundation\Application' not found"**
→ Missing vendor folder. Run `composer install`

**"The stream or file could not be opened"**
→ Storage permissions. Run `chmod -R 755 storage`

**"No application encryption key has been specified"**
→ APP_KEY not set. Check test-env.php results

**"SQLSTATE[HY000] [1045] Access denied"**
→ Wrong database credentials in .htaccess

**"Base table or view not found"**
→ Database not imported. Import database_import.sql

---

## 🎯 Quick Fix Commands

If you have Terminal access:

```bash
cd ~/public_html/tarek

# Install dependencies
composer install --no-dev --optimize-autoloader

# Fix permissions
chmod -R 755 storage bootstrap/cache

# Create required directories
mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p storage/logs bootstrap/cache

# Set permissions again
chmod -R 755 storage bootstrap/cache

# Test
php artisan --version
```

If it shows Laravel version, PHP is working!

---

## 📞 After Checking test-env.php

**Tell me what you see at:**
https://tarek.arvixi.net/test-env.php

The results will tell us exactly what's wrong!

---

## ⚠️ Security Note

**DELETE test-env.php after debugging!**

```bash
rm ~/public_html/tarek/public/test-env.php
```

Or via File Manager:
Delete: `public_html/tarek/public/test-env.php`

---

**Start with visiting the test file - it will show exactly what's wrong!** 🔍

