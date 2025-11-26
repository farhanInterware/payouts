# Logging System Documentation

## Overview

The application uses a comprehensive logging system that maintains daily logs for each module and API interactions. All logs are stored in separate files organized by module type.

## Log Structure

### Log Directories

All logs are stored in `storage/logs/` with the following structure:

```
storage/logs/
├── api/              # API requests and responses
│   └── api-YYYY-MM-DD.log
├── activity/         # User and admin activities
│   └── activity-YYYY-MM-DD.log
├── transactions/     # Transaction-related logs
│   └── transactions-YYYY-MM-DD.log
├── callbacks/        # Payment provider callbacks
│   └── callbacks-YYYY-MM-DD.log
├── payment/          # Payment module logs
│   └── payment-YYYY-MM-DD.log
├── user/             # User module logs
│   └── user-YYYY-MM-DD.log
└── admin/            # Admin module logs
    └── admin-YYYY-MM-DD.log
```

## Log Channels

### 1. API Logs (`api`)
- **Location**: `storage/logs/api/api-YYYY-MM-DD.log`
- **Purpose**: Logs all API requests and responses
- **Logged Events**:
  - API requests (before sending)
  - API responses (after receiving)
  - API errors
  - Status codes
  - Request/response payloads

### 2. Activity Logs (`activity`)
- **Location**: `storage/logs/activity/activity-YYYY-MM-DD.log`
- **Purpose**: Logs user and admin activities
- **Logged Events**:
  - Login/logout
  - Registration
  - Dashboard views
  - Transaction views
  - User management actions
  - Admin actions

### 3. Transaction Logs (`transactions`)
- **Location**: `storage/logs/transactions/transactions-YYYY-MM-DD.log`
- **Purpose**: Logs transaction-related events
- **Logged Events**:
  - Transaction creation
  - Status updates
  - Callback updates
  - Result form updates

### 4. Callback Logs (`callbacks`)
- **Location**: `storage/logs/callbacks/callbacks-YYYY-MM-DD.log`
- **Purpose**: Logs payment provider callbacks
- **Logged Events**:
  - JSON callbacks received
  - Form callbacks received
  - Signature verification status
  - Callback data

### 5. Payment Logs (`payment`)
- **Location**: `storage/logs/payment/payment-YYYY-MM-DD.log`
- **Purpose**: Payment module specific logs
- **Logged Events**: Payment-related activities

### 6. User Logs (`user`)
- **Location**: `storage/logs/user/user-YYYY-MM-DD.log`
- **Purpose**: User module specific logs
- **Logged Events**: User panel activities

### 7. Admin Logs (`admin`)
- **Location**: `storage/logs/admin/admin-YYYY-MM-DD.log`
- **Purpose**: Admin module specific logs
- **Logged Events**: Admin panel activities

## Log Retention

- **Retention Period**: 30 days
- **Rotation**: Daily (new file created each day)
- **Format**: Laravel daily log format with timestamps

## Usage Examples

### Logging API Request
```php
LoggingService::logApiRequest('Payment', '/payout', $requestData, 'POST');
```

### Logging API Response
```php
LoggingService::logApiResponse('Payment', '/payout', $responseData, 200);
```

### Logging API Error
```php
LoggingService::logApiError('Payment', '/payout', $errorMessage, $context);
```

### Logging Activity
```php
LoggingService::logActivity('User', 'view_dashboard', [], auth()->id());
```

### Logging Transaction
```php
LoggingService::logTransaction('created', $transactionData, auth()->id());
```

### Logging Callback
```php
LoggingService::logCallback('JSON', $callbackData, true); // true = verified
```

## Log Format

Each log entry includes:
- **Timestamp**: ISO 8601 format
- **Module**: Module name (Payment, User, Admin, etc.)
- **Action**: Action performed
- **Data**: Relevant data (request, response, context)
- **User ID**: User who performed the action (if applicable)

## What Gets Logged

### Payment API
- ✅ Payout request (full payload with signature)
- ✅ Payout response (status, order_id, operation_id)
- ✅ Status check request
- ✅ Status check response
- ✅ API errors with full context

### Callbacks
- ✅ JSON callbacks (before and after verification)
- ✅ Form callbacks (before and after verification)
- ✅ Signature verification status
- ✅ Transaction updates from callbacks

### Transactions
- ✅ Transaction creation
- ✅ Status updates
- ✅ Status changes (old → new)

### User Activities
- ✅ Dashboard views
- ✅ Transaction list views
- ✅ Transaction detail views
- ✅ Transaction creation attempts

### Admin Activities
- ✅ Dashboard views
- ✅ User list views
- ✅ User detail views
- ✅ Transaction list views
- ✅ Transaction detail views

### Authentication
- ✅ Login attempts (success/failure)
- ✅ Logout
- ✅ Registration

## Viewing Logs

### Via Command Line
```bash
# View today's API logs
tail -f storage/logs/api/api-$(date +%Y-%m-%d).log

# View today's activity logs
tail -f storage/logs/activity/activity-$(date +%Y-%m-%d).log

# View today's transaction logs
tail -f storage/logs/transactions/transactions-$(date +%Y-%m-%d).log

# View today's callback logs
tail -f storage/logs/callbacks/callbacks-$(date +%Y-%m-%d).log
```

### Via File System
Navigate to `storage/logs/` and open the appropriate log file for the date you need.

## Security Notes

- Logs may contain sensitive information (signatures, transaction data)
- Ensure log files have proper file permissions
- Consider restricting access to log directories
- Logs are automatically rotated daily
- Old logs (30+ days) are automatically deleted

## Configuration

Log retention and paths can be configured in `config/logging.php`:

```php
'api' => [
    'driver' => 'daily',
    'path' => storage_path('logs/api/api.log'),
    'days' => 30, // Retention period
],
```

## Troubleshooting

If logs are not being created:
1. Check directory permissions: `storage/logs/` must be writable
2. Check disk space
3. Verify log channels are configured in `config/logging.php`
4. Clear config cache: `php artisan config:clear`

