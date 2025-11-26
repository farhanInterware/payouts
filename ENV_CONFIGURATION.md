# Environment Configuration Guide

## Payment URLs - Automatic from APP_URL

The payment callback URLs are **automatically generated from `APP_URL`** if you don't set them explicitly in your `.env` file.

### How It Works

The `config/payment.php` file automatically builds all payment URLs from `APP_URL`:

```php
'callback_url' => env('PAYMENT_CALLBACK_URL', env('APP_URL') . '/api/payment/callback'),
'result_url' => env('PAYMENT_RESULT_URL', env('APP_URL') . '/payment/result'),
// ... etc
```

### Minimal .env Configuration

You only need to set `APP_URL`:

```env
APP_URL=http://localhost:8000
```

All payment URLs will be automatically generated:
- `PAYMENT_CALLBACK_URL` → `http://localhost:8000/api/payment/callback`
- `PAYMENT_RESULT_URL` → `http://localhost:8000/payment/result`
- `PAYMENT_PROCESSING_URL` → `http://localhost:8000/payment/processing`
- `PAYMENT_SUCCESS_URL` → `http://localhost:8000/payment/success`
- `PAYMENT_FAIL_URL` → `http://localhost:8000/payment/fail`
- `PAYMENT_BACK_URL` → `http://localhost:8000/`

### Override Individual URLs (Optional)

If you need to override a specific URL, you can set it explicitly:

```env
APP_URL=http://localhost:8000
PAYMENT_CALLBACK_URL=https://custom-domain.com/api/payment/callback
```

### Production Example

For production, just set `APP_URL`:

```env
APP_URL=https://payouts.interwarepvt.com
```

All payment URLs will automatically use `https://payouts.interwarepvt.com` as the base.

### Benefits

1. **Single source of truth**: Change `APP_URL` once, all URLs update
2. **Less configuration**: No need to set 6 different URL variables
3. **Fewer errors**: No risk of mismatched URLs
4. **Easy environment switching**: Change `APP_URL` for dev/staging/production

### Required Environment Variables

**Required:**
- `APP_URL` - Base URL for your application

**Optional (auto-generated from APP_URL):**
- `PAYMENT_CALLBACK_URL`
- `PAYMENT_RESULT_URL`
- `PAYMENT_PROCESSING_URL`
- `PAYMENT_SUCCESS_URL`
- `PAYMENT_FAIL_URL`
- `PAYMENT_BACK_URL`

**Required for Payment API:**
- `PAYMENT_API_URL`
- `PAYMENT_MERCHANT_ID`
- `PAYMENT_MERCHANT_CONTROL_VALUE`

