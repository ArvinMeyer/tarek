# Setup Using .htaccess Only (No .env file)

## 📁 File Structure for Subdomain

Your subdomain: `tarek.arvixi.net`
Your folder: `public_html/tarek/`

---

## ✅ **Setup Steps**

### Step 1: Upload Files

Upload all Laravel files to: `public_html/tarek/`

### Step 2: Configure Subdomain Document Root

1. Go to cPanel → **Subdomains**
2. Find `tarek.arvixi.net`
3. Change **Document Root** to: `public_html/tarek/public`
4. Save

### Step 3: Replace .htaccess Files

#### **Root .htaccess** 
File: `public_html/tarek/.htaccess`

Replace with the content from `.htaccess.subdomain` file

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

<IfModule mod_env.c>
    SetEnv APP_NAME "PrintItMat"
    SetEnv APP_ENV production
    SetEnv APP_KEY base64:8dQ7xw9W8CbKk5UlZjKMfGzKWz0XvP8qN5YzLtQmRkE=
    SetEnv APP_DEBUG false
    SetEnv APP_URL https://tarek.arvixi.net
    
    SetEnv DB_CONNECTION mysql
    SetEnv DB_HOST localhost
    SetEnv DB_PORT 3306
    SetEnv DB_DATABASE u624844894_tarek
    SetEnv DB_USERNAME u624844894_tarek
    SetEnv DB_PASSWORD Gofuckurself123*
    
    SetEnv SESSION_DRIVER database
    SetEnv SESSION_LIFETIME 120
    SetEnv QUEUE_CONNECTION sync
    SetEnv CACHE_DRIVER file
</IfModule>
```

#### **Public .htaccess**
File: `public_html/tarek/public/.htaccess`

Replace with the content from `public/.htaccess.subdomain` file

(Contains all Laravel routing + environment variables)

### Step 4: Delete .env File (Optional)

Since all config is in .htaccess, you can delete:
- `public_html/tarek/.env` (if it exists)

Laravel will read from .htaccess environment variables instead.

### Step 5: Fix Permissions

Via Terminal:
```bash
cd ~/public_html/tarek
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

Via File Manager:
- Right-click `storage` → Permissions → 755 (recursive)
- Right-click `bootstrap/cache` → Permissions → 755 (recursive)

### Step 6: Import Database

1. Open phpMyAdmin in cPanel
2. Select database: `u624844894_tarek`
3. Import → Choose `database_import.sql`
4. Click Go

### Step 7: Test

Visit: https://tarek.arvixi.net

You should see the login page!

**Login:**
- Username: `admin`
- Password: `admin123`

---

## 🔧 **Important Notes**

### Environment Variables in .htaccess

All Laravel configuration is set using `SetEnv` in .htaccess:

```apache
<IfModule mod_env.c>
    SetEnv VARIABLE_NAME value
</IfModule>
```

These variables are read by Laravel just like .env file.

### Security Warning ⚠️

Since .htaccess is in the web root, make sure it's protected:

```apache
<FilesMatch "^\.ht">
    Order allow,deny
    Deny from all
</FilesMatch>
```

This is already included in the provided .htaccess files.

### Updating Configuration

To change any setting (like email or database), edit the `.htaccess` file in:
- `public_html/tarek/public/.htaccess`

Then restart Apache (or just reload the page).

---

## 📋 **Complete .htaccess Configuration**

### **Variables Set in .htaccess:**

```apache
# Application
APP_NAME="PrintItMat"
APP_ENV=production
APP_KEY=base64:8dQ7xw9W8CbKk5UlZjKMfGzKWz0XvP8qN5YzLtQmRkE=
APP_DEBUG=false
APP_URL=https://tarek.arvixi.net

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u624844894_tarek
DB_USERNAME=u624844894_tarek
DB_PASSWORD=Gofuckurself123*

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync
CACHE_DRIVER=file

# Mail (configure when needed)
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls

# IMAP (configure when needed)
IMAP_HOST=imap.hostinger.com
IMAP_PORT=993
IMAP_ENCRYPTION=ssl
```

---

## 🚀 **Advantages of .htaccess Setup**

✅ No .env file needed
✅ All config in one place
✅ Easy to backup (just copy .htaccess)
✅ Works on all shared hosting
✅ Server environment variables

---

## ⚠️ **Disadvantages**

❌ Database password visible in .htaccess (make sure it's protected)
❌ Need to restart/reload Apache after changes
❌ Harder to have different environments (local vs production)

---

## 🔍 **Troubleshooting**

### "500 Internal Server Error"

Check if `mod_env` is enabled:
- Some hosts disable `SetEnv` 
- Contact support to enable it

Alternative: Use `.htaccess` with `php_value` instead:
```apache
php_value auto_prepend_file "/path/to/set-env.php"
```

### Variables Not Working

Test if environment variables are set:

Create `test.php` in public folder:
```php
<?php
echo getenv('APP_NAME');
echo getenv('DB_DATABASE');
phpinfo(); // Check environment section
?>
```

Visit: https://tarek.arvixi.net/test.php

Delete after testing!

---

## 📁 **Final File Structure**

```
public_html/tarek/
├── .htaccess           ← All environment variables here
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
│   ├── .htaccess      ← Routes + environment variables
│   └── index.php
├── resources/
├── routes/
├── storage/           ← 755 permissions
├── vendor/
└── composer.json
```

**NO .env file needed!**

---

## ✅ **Quick Reference**

**Files to replace:**
1. `public_html/tarek/.htaccess` → Use `.htaccess.subdomain`
2. `public_html/tarek/public/.htaccess` → Use `public/.htaccess.subdomain`

**Delete:**
- `public_html/tarek/.env` (not needed)

**Fix permissions:**
- `storage` → 755
- `bootstrap/cache` → 755

**Import database:**
- phpMyAdmin → Import `database_import.sql`

**Test:**
- Visit: https://tarek.arvixi.net
- Login: admin / admin123

---

**That's it! Everything is configured via .htaccess!** 🎉

