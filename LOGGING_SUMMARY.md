# Logging Summary - Status and Callback Functions

## ✅ Status API (`/status`)

### What's Logged:

1. **API Request** (via `LoggingService::logApiRequest`)
   - Module: Payment
   - Endpoint: `/status`
   - Method: POST
   - Request data (decoded array)
   - Timestamp
   - **Location**: `storage/logs/api/api-YYYY-MM-DD.log`

2. **API Request JSON String** (for signature debugging)
   - Actual JSON string being sent to API
   - **Location**: `storage/logs/api/api-YYYY-MM-DD.log`

3. **API Response** (via `LoggingService::logApiResponse`)
   - Module: Payment
   - Endpoint: `/status`
   - Status code
   - Response data
   - Timestamp
   - **Location**: `storage/logs/api/api-YYYY-MM-DD.log`

4. **Activity Log** (via `LoggingService::logActivity`)
   - Module: Payment
   - Action: `check_status`
   - User ID
   - Order ID, Operation ID
   - **Location**: `storage/logs/activity/activity-YYYY-MM-DD.log`

5. **Transaction Update** (via `LoggingService::logTransaction`)
   - Action: `status_updated`
   - Transaction ID
   - Order ID
   - Old status → New status
   - User ID
   - **Location**: `storage/logs/transactions/transactions-YYYY-MM-DD.log`

6. **API Errors** (via `LoggingService::logApiError`)
   - Module: Payment
   - Endpoint: `/status`
   - Error message
   - HTTP status code
   - API error response
   - Order ID, Operation ID
   - **Location**: `storage/logs/api/api-YYYY-MM-DD.log`

---

## ✅ Callback API (`/api/payment/callback`) - JSON

### What's Logged:

1. **Incoming Callback** (via `LoggingService::logCallback`)
   - Type: JSON
   - Verified: false (before verification)
   - Full callback data
   - Timestamp
   - **Location**: `storage/logs/callbacks/callbacks-YYYY-MM-DD.log`

2. **Verified Callback** (via `LoggingService::logCallback`)
   - Type: JSON
   - Verified: true (after successful verification)
   - Full callback data
   - Timestamp
   - **Location**: `storage/logs/callbacks/callbacks-YYYY-MM-DD.log`

3. **Transaction Update** (via `LoggingService::logTransaction`)
   - Action: `callback_update`
   - Transaction ID
   - Order ID
   - Old status → New status
   - Source: `callback`
   - User ID
   - **Location**: `storage/logs/transactions/transactions-YYYY-MM-DD.log`

4. **Signature Verification Failure** (via `Log::warning`)
   - Warning when signature verification fails
   - Callback data
   - **Location**: `storage/logs/laravel.log`

5. **API Errors** (via `LoggingService::logApiError`)
   - Module: Payment
   - Endpoint: `/callback`
   - Error message
   - Exception type, file, line
   - Merchant Order ID, Order ID
   - **Location**: `storage/logs/api/api-YYYY-MM-DD.log`

---

## ✅ Result Form (`/payment/result`) - Form POST

### What's Logged:

1. **Incoming Form Callback** (via `LoggingService::logCallback`)
   - Type: FORM
   - Verified: false (before verification)
   - Full form data
   - Timestamp
   - **Location**: `storage/logs/callbacks/callbacks-YYYY-MM-DD.log`

2. **Verified Form Callback** (via `LoggingService::logCallback`)
   - Type: FORM
   - Verified: true (after successful verification)
   - Full form data
   - Timestamp
   - **Location**: `storage/logs/callbacks/callbacks-YYYY-MM-DD.log`

3. **Transaction Update** (via `LoggingService::logTransaction`)
   - Action: `result_update`
   - Transaction ID
   - Order ID
   - Old status → New status
   - Source: `result_form`
   - User ID
   - **Location**: `storage/logs/transactions/transactions-YYYY-MM-DD.log`

4. **Signature Verification Failure** (via `Log::warning`)
   - Warning when signature verification fails
   - Form data
   - **Location**: `storage/logs/laravel.log`

5. **API Errors** (via `LoggingService::logApiError`)
   - Module: Payment
   - Endpoint: `/result`
   - Error message
   - Exception type, file, line
   - Merchant Order ID, Order ID
   - **Location**: `storage/logs/api/api-YYYY-MM-DD.log`

---

## 📁 Log File Locations

All logs are stored in `storage/logs/` with daily rotation:

- **API Logs**: `storage/logs/api/api-YYYY-MM-DD.log`
  - API requests, responses, errors
  - JSON strings for signature debugging

- **Activity Logs**: `storage/logs/activity/activity-YYYY-MM-DD.log`
  - User/admin activities
  - Status check requests

- **Transaction Logs**: `storage/logs/transactions/transactions-YYYY-MM-DD.log`
  - Transaction creation
  - Status updates
  - Callback updates

- **Callback Logs**: `storage/logs/callbacks/callbacks-YYYY-MM-DD.log`
  - JSON callbacks (verified/unverified)
  - Form callbacks (verified/unverified)

- **Laravel Logs**: `storage/logs/laravel.log`
  - General application logs
  - Signature verification warnings

---

## ✅ Summary

**All functions are properly logged:**
- ✅ Status API - Request, Response, Errors, Transaction Updates
- ✅ JSON Callback - Incoming, Verified, Transaction Updates, Errors
- ✅ Form Callback - Incoming, Verified, Transaction Updates, Errors

**Log retention**: 30 days (configured in `config/logging.php`)

