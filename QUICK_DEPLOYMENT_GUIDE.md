# Quick Deployment Guide - Hostinger Shared Hosting

## 🚀 Essential Steps for Live Deployment

### 1. **Update `.env` File**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://payouts.interwarepvt.com

# Update database credentials
DB_DATABASE=your_live_database
DB_USERNAME=your_live_db_user
DB_PASSWORD=your_live_db_password

# Verify payment API URL (sandbox or production)
PAYMENT_API_URL=https://sandbox-api.debitly.tech
```

### 2. **Set File Permissions**

Via SSH or File Manager:
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 3. **Run Commands**

Via SSH (if available) or use Hostinger's terminal:
```bash
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. **Point Domain to `public` Folder**

In Hostinger cPanel:
- Go to **Domains** → **Manage**
- Set Document Root to: `public_html/payouts.interwarepvt.com/public`
- Or use the root `.htaccess` file I created (redirects to public)

### 5. **Verify Callback URLs**

After deployment, your callback URLs will be:
- ✅ `https://payouts.interwarepvt.com/api/payment/callback`
- ✅ `https://payouts.interwarepvt.com/payment/result`
- ✅ `https://payouts.interwarepvt.com/payment/success`
- ✅ `https://payouts.interwarepvt.com/payment/fail`

These are automatically generated from `APP_URL` - no changes needed!

---

## ✅ Files Created/Updated for Deployment

1. ✅ `public/.htaccess` - Laravel routing rules
2. ✅ `.htaccess` (root) - Redirects to public folder if needed
3. ✅ `DEPLOYMENT_CHECKLIST.md` - Full deployment guide

---

## 🔍 Quick Test Checklist

After deployment, test:
- [ ] Homepage loads
- [ ] Login works
- [ ] Can create transaction
- [ ] Callback URL is accessible: `https://payouts.interwarepvt.com/api/payment/callback`
- [ ] No 500 errors
- [ ] Logs are being written

---

## ⚠️ Important Notes

1. **SSL Required**: Ensure HTTPS is enabled (Hostinger usually provides free SSL)
2. **PHP Version**: Must be PHP 8.1 or higher
3. **Database**: Create database in Hostinger cPanel first
4. **Composer**: Run `composer install --no-dev --optimize-autoloader` on server

---

## 🆘 If Something Doesn't Work

1. Check `storage/logs/laravel.log` for errors
2. Verify file permissions (755 or 775)
3. Check `.env` file exists and is configured
4. Verify `APP_KEY` is set
5. Clear cache: `php artisan config:clear && php artisan cache:clear`

