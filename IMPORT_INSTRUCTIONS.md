# Database Import Instructions

## ✅ Your Database Configuration

Your `.env` file has been configured with:

```
DB_HOST=localhost
DB_DATABASE=u624844894_tarek
DB_USERNAME=u624844894_tarek
DB_PASSWORD=Gofuckurself123*
```

---

## 📥 How to Import SQL File into phpMyAdmin

### Step 1: Login to phpMyAdmin

1. Go to your Hostinger cPanel
2. Find and click **phpMyAdmin**
3. You'll be logged in automatically

### Step 2: Select Your Database

1. On the left sidebar, click on your database: **`u624844894_tarek`**
2. The database should now be highlighted

### Step 3: Import the SQL File

1. Click on the **Import** tab at the top
2. Click **Choose File** button
3. Select the file: **`database_import.sql`**
4. Scroll down and click **Go** button at the bottom

### Step 4: Wait for Import

- The import will take 10-30 seconds
- You'll see a success message: "Import has been successfully finished"
- If you see any errors, check the error message

### Step 5: Verify Tables Created

1. Click on your database name in the left sidebar
2. You should see these tables:
   - ✅ users (4 default users)
   - ✅ customers
   - ✅ quotations
   - ✅ invoices
   - ✅ purchase_orders
   - ✅ emails
   - ✅ settings (25 default settings)
   - ✅ audit_logs
   - ✅ counters (7 counters)
   - ✅ sessions
   - ✅ cache
   - ✅ migrations
   - And more...

---

## 🔐 Default Login Credentials

After importing, you can login with:

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`

### Manager Account
- **Username**: `manager`
- **Password**: `manager123`

### Staff Account
- **Username**: `staff`
- **Password**: `staff123`

### Viewer Account
- **Username**: `viewer`
- **Password**: `viewer123`

⚠️ **IMPORTANT**: You must change all passwords after first login!

---

## 🚀 Next Steps After Import

### 1. Generate Application Key

Via SSH or Terminal in cPanel:
```bash
cd public_html
php artisan key:generate --force
```

Or manually:
- Run `php artisan key:generate` on local machine
- Copy the generated key
- Update `APP_KEY=` in your `.env` file on server

### 2. Update Your Domain

Edit `.env` file and change:
```env
APP_URL=https://yourdomain.com
```

### 3. Set File Permissions

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 4. Create Storage Symlink

```bash
php artisan storage:link
```

### 5. Test Login

1. Visit your domain: `https://yourdomain.com`
2. You should see the login page
3. Login with admin credentials above
4. You'll be prompted to change password

---

## ❓ Troubleshooting

### "Table already exists" Error
- Your database already has tables
- Option 1: Drop all existing tables first
- Option 2: Use a fresh database

### "Access Denied" Error
- Check your database credentials in `.env`
- Verify username/password in Hostinger cPanel

### "File Too Large" Error
1. In phpMyAdmin, click **Import**
2. Look for "Max: XX MB" near file upload
3. If your file is larger, split it or increase PHP limits

### Cannot Login After Import
1. Make sure all tables were created successfully
2. Check that `users` table has 4 rows
3. Verify `.env` database credentials are correct
4. Check `storage/logs/laravel.log` for errors

---

## 📊 What Gets Created

### Tables (16 total)
1. **users** - 4 default users
2. **customers** - Empty (ready for your data)
3. **quotations** - Empty
4. **quotation_items** - Empty
5. **invoices** - Empty
6. **invoice_items** - Empty
7. **invoice_payments** - Empty
8. **purchase_orders** - Empty
9. **purchase_order_items** - Empty
10. **emails** - Empty
11. **email_attachments** - Empty
12. **customer_files** - Empty
13. **settings** - 25 default settings
14. **counters** - 7 counters for auto-numbering
15. **audit_logs** - Empty (will track all actions)
16. **sessions** - For user sessions
17. **cache** - For caching
18. **migrations** - Track database version

### Default Data
- ✅ 4 Users (admin, manager, staff, viewer)
- ✅ 25 System settings
- ✅ 7 Counters (for quotation/invoice numbering)
- ✅ 11 Migration records

---

## 🎉 Success!

Once imported successfully:
1. All database tables are created
2. Default users are ready
3. System settings are configured
4. You can start using the application immediately!

---

## 📞 Need Help?

Check these files for more information:
- `README.md` - Complete documentation
- `DEPLOYMENT.md` - Full deployment guide
- `QUICK_START.md` - Quick setup guide

If you see errors in phpMyAdmin:
- Read the error message carefully
- Check if tables already exist
- Verify database user has proper permissions
- Make sure database is empty before importing

---

**File Location**: `database_import.sql` (in your project root)

**Import this file via phpMyAdmin and you're done!** 🚀

