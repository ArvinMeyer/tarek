# Fix Subdomain Document Root Issue

## 🚨 Problem

Your URL shows: `https://tarek.arvixi.net/public/index.php`

This means subdomain is pointing to: `public_html/tarek/`
But it SHOULD point to: `public_html/tarek/public/`

---

## ✅ Solution 1: Change Subdomain Document Root (RECOMMENDED)

### Step 1: Go to cPanel → Subdomains

1. Login to cPanel
2. Click **Subdomains** (in Domains section)
3. Find `tarek.arvixi.net` in the list

### Step 2: Change Document Root

Look for the **Document Root** column.

**Currently shows:** `public_html/tarek`

**Change it to:** `public_html/tarek/public`

Click the pencil/edit icon and update it.

### Step 3: Save & Test

After saving, visit: `https://tarek.arvixi.net` (without /public)

Should now show the login page!

---

## ✅ Solution 2: If You Can't Change Document Root

If Hostinger won't let you change the document root, use this alternative setup:

### Create Special .htaccess in tarek folder

File: `public_html/tarek/.htaccess`

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Force www or non-www (optional)
    # RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
    # RewriteRule ^(.*)$ https://%1/$1 [R=301,L]

    # Redirect to public folder
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L]

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
</IfModule>

# Set all Laravel environment variables
<IfModule mod_env.c>
    SetEnv APP_NAME "PrintItMat"
    SetEnv APP_ENV production
    SetEnv APP_KEY base64:8dQ7xw9W8CbKk5UlZjKMfGzKWz0XvP8qN5YzLtQmRkE=
    SetEnv APP_DEBUG false
    SetEnv APP_URL https://tarek.arvixi.net
    
    # Database Configuration
    SetEnv DB_CONNECTION mysql
    SetEnv DB_HOST localhost
    SetEnv DB_PORT 3306
    SetEnv DB_DATABASE u624844894_tarek
    SetEnv DB_USERNAME u624844894_tarek
    SetEnv DB_PASSWORD Gofuckurself123*
    
    # Session & Cache
    SetEnv SESSION_DRIVER database
    SetEnv SESSION_LIFETIME 120
    SetEnv QUEUE_CONNECTION sync
    SetEnv CACHE_DRIVER file
    
    # Mail Configuration
    SetEnv MAIL_MAILER smtp
    SetEnv MAIL_HOST smtp.hostinger.com
    SetEnv MAIL_PORT 587
    SetEnv MAIL_ENCRYPTION tls
    
    # IMAP Configuration
    SetEnv IMAP_HOST imap.hostinger.com
    SetEnv IMAP_PORT 993
    SetEnv IMAP_ENCRYPTION ssl
</IfModule>

# Disable directory browsing
Options -Indexes

# Security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>
```

This will automatically redirect to the public folder.

---

## ✅ Solution 3: Move Everything to Root (Last Resort)

If neither above works, move all files from `public` folder to root:

### Step 1: Move Files

Move everything from: `public_html/tarek/public/*`
To: `public_html/tarek/`

This includes:
- index.php
- .htaccess
- All asset folders

### Step 2: Update index.php

Edit `public_html/tarek/index.php`

**Find these lines (around line 16-18):**
```php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
```

**They're already correct** (no changes needed since __DIR__ is root)

### Step 3: Use This .htaccess

File: `public_html/tarek/.htaccess`

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# All environment variables
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

# Disable directory browsing
Options -Indexes

# Security
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>
```

---

## 🔍 Check Current Setup

### Via cPanel File Manager:

1. Go to `public_html/tarek/`
2. Check if you see these folders:
   - ✅ app/
   - ✅ bootstrap/
   - ✅ config/
   - ✅ public/
   - ✅ storage/
   - ✅ vendor/

### If public/ folder exists:

**Subdomain document root MUST be:** `public_html/tarek/public`

### Check Subdomain Settings:

cPanel → Subdomains → Look at Document Root column

Should show: `public_html/tarek/public` (not just `public_html/tarek`)

---

## 🎯 Quick Fix Steps

**Choose the easiest option:**

### Option A: Fix Document Root (Best)
1. cPanel → Subdomains
2. Change root to: `public_html/tarek/public`
3. Test: https://tarek.arvixi.net

### Option B: Auto-Redirect (If can't change root)
1. Replace `public_html/tarek/.htaccess` with Solution 2 above
2. Test: https://tarek.arvixi.net

### Option C: Move Files (Last resort)
1. Move public/* to tarek/
2. Update .htaccess
3. Test: https://tarek.arvixi.net

---

## 💡 After Fixing

Once you fix the document root, the URL should be:
- ✅ `https://tarek.arvixi.net` (login page)
- ❌ NOT `https://tarek.arvixi.net/public/index.php`

---

## 🆘 Still Getting 500 Error?

Enable debug to see actual error:

In your .htaccess, change:
```apache
SetEnv APP_DEBUG true
SetEnv APP_ENV local
```

Then visit the site and you'll see the detailed error message.

After fixing, change back to:
```apache
SetEnv APP_DEBUG false
SetEnv APP_ENV production
```

---

**Try Option A first (change document root) - it's the cleanest solution!**

