# Production Deployment Checklist - Hostinger Shared Hosting

## ✅ Critical Changes Required

### 1. **Environment Configuration (`.env` file)**

Update your `.env` file on the live server with these values:

```env
APP_NAME=Debitly
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://payouts.interwarepvt.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Payment API Configuration
PAYMENT_API_URL=https://sandbox-api.debitly.tech
# Change to production API URL when ready:
# PAYMENT_API_URL=https://api.debitly.tech

PAYMENT_MERCHANT_ID=85
PAYMENT_MERCHANT_CONTROL_VALUE=your_merchant_control_value

# Callback URLs (automatically uses APP_URL)
# These will be: https://payouts.interwarepvt.com/api/payment/callback
# etc.
```

### 2. **Public Directory Setup**

On shared hosting, you typically need to point your domain to the `public` folder:

**Option A: Point domain to `public` folder (Recommended)**
- In Hostinger cPanel, set document root to: `/public_html/payouts.interwarepvt.com/public`
- Or if using subdomain: `/public_html/payouts/public`

**Option B: Use `.htaccess` redirect (if you can't change document root)**
Create `.htaccess` in the root directory:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### 3. **File Permissions**

Set correct permissions on the live server:

```bash
# Storage and cache directories (755 or 775)
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# If 755 doesn't work, try 775
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 4. **Database Setup**

Run migrations on the live server:

```bash
php artisan migrate --force
php artisan db:seed --force
```

**Note:** On shared hosting, you might need to run these via SSH or use a database management tool.

### 5. **Generate Application Key**

If you haven't already:

```bash
php artisan key:generate
```

### 6. **Clear and Cache Configuration**

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# For production, cache config and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. **Storage Link**

Create symbolic link for storage (if needed):

```bash
php artisan storage:link
```

### 8. **Update Callback URLs**

The callback URLs will automatically use `APP_URL`, but verify they're correct:
- Callback URL: `https://payouts.interwarepvt.com/api/payment/callback`
- Result URL: `https://payouts.interwarepvt.com/payment/result`
- Success URL: `https://payouts.interwarepvt.com/payment/success`
- Fail URL: `https://payouts.interwarepvt.com/payment/fail`
- Processing URL: `https://payouts.interwarepvt.com/payment/processing`

### 9. **SSL Certificate**

Ensure your domain has SSL enabled (HTTPS). Hostinger usually provides free SSL certificates.

### 10. **PHP Version**

Verify PHP version on Hostinger:
- Required: PHP >= 8.1
- Check in cPanel → Select PHP Version

### 11. **Required PHP Extensions**

Ensure these are enabled:
- `php-curl`
- `php-mbstring`
- `php-xml`
- `php-json`
- `php-mysql` or `php-pgsql`
- `php-openssl`
- `php-zip`

### 12. **Composer Autoload**

On shared hosting, you might need to run:

```bash
composer install --no-dev --optimize-autoloader
```

### 13. **Log Files**

Ensure log directories exist and are writable:
- `storage/logs/api/`
- `storage/logs/activity/`
- `storage/logs/transactions/`
- `storage/logs/callbacks/`
- `storage/logs/payment/`
- `storage/logs/user/`
- `storage/logs/admin/`

### 14. **Security Settings**

Update `.env`:
```env
APP_DEBUG=false
LOG_LEVEL=error
```

### 15. **.htaccess in Public Directory**

Ensure `public/.htaccess` exists and is configured correctly for Laravel.

---

## 🔍 Verification Steps

After deployment, verify:

1. ✅ Homepage loads correctly
2. ✅ Login page works
3. ✅ Can create a transaction
4. ✅ Callback URLs are accessible (test with a webhook tester)
5. ✅ Database connections work
6. ✅ File uploads/storage works
7. ✅ Logs are being written
8. ✅ No errors in `storage/logs/laravel.log`

---

## 🚨 Common Issues on Shared Hosting

### Issue 1: 500 Internal Server Error
- Check file permissions (storage, bootstrap/cache)
- Check `.env` file exists and is configured
- Check `APP_KEY` is set
- Check PHP version compatibility

### Issue 2: Callback Not Working
- Verify callback URL is accessible from external sources
- Check if shared hosting blocks certain IPs
- Verify SSL certificate is valid
- Check if mod_rewrite is enabled

### Issue 3: Database Connection Failed
- Verify database credentials in `.env`
- Check if database host is `localhost` or needs specific host
- Verify database user has proper permissions

### Issue 4: Storage Not Writable
- Set permissions: `chmod -R 775 storage`
- Check if SELinux is blocking (unlikely on shared hosting)

---

## 📝 Additional Notes

1. **API Environment**: If you're still testing, keep `PAYMENT_API_URL` as sandbox. Change to production URL when ready.

2. **Merchant Credentials**: Update `PAYMENT_MERCHANT_ID` and `PAYMENT_MERCHANT_CONTROL_VALUE` with your production credentials when moving from sandbox.

3. **Backup**: Always backup your database before running migrations on production.

4. **Monitoring**: Set up error monitoring (consider Laravel Telescope or similar for production).

---

## 🔗 Quick Reference

- **Live URL**: https://payouts.interwarepvt.com
- **Admin Login**: `/login` (use admin credentials from seeder)
- **User Login**: `/login` (use user credentials from seeder)

