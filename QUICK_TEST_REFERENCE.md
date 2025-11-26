# Quick Callback Testing Reference

## Quick Test Commands

```bash
# Test valid callback (default)
php test_callback.php

# Test different scenarios
php test_callback.php approved
php test_callback.php declined
php test_callback.php invalid-signature
php test_callback.php missing-fields
php test_callback.php no-transaction
php test_callback.php duplicate
```

## Quick cURL Test

```bash
# Replace with your actual signed payload from test_callback.php
curl -X POST http://localhost:8000/api/payment/callback \
  -H "Content-Type: application/json" \
  -d '{"signature":"...","merchant_order_id":"...","order_desc":"...","amount":"100.00","currency":"USD","merchant":{"id":85,"custom_data":{}},"order_id":"...","operation_id":"...","operation_type":"payout","status":"approved","pay_method":"card","created_at":"2024-01-01T12:00:00+0000","finished_at":"2024-01-01T12:00:00+0000"}'
```

## Check Logs

```bash
# Callback logs
tail -f storage/logs/callbacks/callbacks-$(date +%Y-%m-%d).log

# Transaction logs
tail -f storage/logs/transactions/transactions-$(date +%Y-%m-%d).log

# All logs
tail -f storage/logs/laravel.log
```

## Expected Responses

### Success
```json
{
  "success": true,
  "message": "Callback processed"
}
```

### Invalid Signature
```json
{
  "success": false,
  "message": "Invalid signature"
}
```

### Missing Fields
```json
{
  "success": false,
  "message": "Missing required fields: merchant_order_id, order_desc"
}
```

### Transaction Not Found
```json
{
  "success": false,
  "message": "Transaction not found"
}
```

## Verify Transaction Update

```sql
SELECT id, merchant_order_id, status, error_message, finished_at, updated_at
FROM transactions
WHERE merchant_order_id = 'your-order-id'
ORDER BY updated_at DESC;
```

## Test Checklist

- [ ] Valid callback with existing transaction
- [ ] Approved status callback
- [ ] Declined status callback
- [ ] Invalid signature handling
- [ ] Missing required fields
- [ ] Non-existent transaction
- [ ] Duplicate callback (idempotency)
- [ ] Check logs for all scenarios
- [ ] Verify database updates

