# Callback Database Updates

## Overview
When a callback is received from the payment gateway, the system updates the `transactions` table with the latest information from the callback.

## Database Updates

### Transaction Record Updates

When a valid callback is received (with correct signature and existing transaction), the following fields are updated in the `transactions` table:

#### Basic Transaction Info
- **`order_id`** - Order ID assigned by payment gateway
- **`operation_id`** - Unique operation ID from payment gateway
- **`operation_type`** - Type of operation (payment, payout, refund, etc.)
- **`status`** - Current status (processing, approved, declined, expired)
- **`pay_method`** - Payment method used (card, sepa, etc.)

#### Amount & Currency
- **`amount`** - Transaction amount
- **`currency`** - Currency code (USD, EUR, etc.)
- **`initial_amount`** - Initial amount from approved operation
- **`total_refunded_amount`** - Total amount refunded

#### Error Information (if declined)
- **`error_message`** - Reason for decline (if status is declined)
- **`error_code`** - Error code from payment gateway (if declined)

#### Customer & Merchant Info
- **`customer_id`** - Customer ID from merchant's system
- **`merchant_id`** - Merchant ID from payment gateway
- **`merchant_custom_data`** - Custom merchant data (JSON)

#### Payment Details (JSON fields)
- **`requisites`** - Payment requisites (card details, bank info, etc.) - JSON
- **`bin_data`** - BIN data (card brand, country, bank name) - JSON

#### Timestamps
- **`finished_at`** - Timestamp when operation reached final status
- **`updated_at`** - Automatically updated by Laravel

#### Order Description
- **`order_desc`** - Order description from callback

## Update Conditions

### When Updates Occur

1. **Valid Signature**: Callback must have valid signature
2. **Transaction Exists**: Transaction must be found by `merchant_order_id` or `order_id`
3. **Not Duplicate**: If callback data is identical to current transaction, no update occurs (idempotency)

### When Updates DON'T Occur

1. **Invalid Signature**: Transaction is NOT updated (security)
2. **Transaction Not Found**: No update (transaction doesn't exist)
3. **Duplicate Callback**: No update if data is identical (idempotency check)
4. **Missing Required Fields**: No update if required fields are missing

## Example Update

### Before Callback
```sql
status: 'processing'
error_message: NULL
error_code: NULL
finished_at: NULL
updated_at: '2025-11-26 10:47:20'
```

### After Callback (Approved)
```sql
status: 'approved'
error_message: NULL
error_code: NULL
finished_at: '2025-11-26 10:49:10'
updated_at: '2025-11-26 10:49:10'
```

### After Callback (Declined)
```sql
status: 'declined'
error_message: 'General decline'
error_code: '5118'
finished_at: '2025-11-26 10:49:10'
updated_at: '2025-11-26 10:49:10'
```

## Code Reference

The update happens in `app/Http/Controllers/PaymentController.php` in the `callback()` method:

```php
// Prepare update data
$updateData = [
    'order_id' => $callbackData['order_id'] ?? $transaction->order_id,
    'operation_id' => $callbackData['operation_id'] ?? $transaction->operation_id,
    'operation_type' => $callbackData['operation_type'] ?? $transaction->operation_type,
    'status' => $callbackData['status'] ?? $transaction->status,
    'pay_method' => $callbackData['pay_method'] ?? $transaction->pay_method,
    'order_desc' => $callbackData['order_desc'] ?? $transaction->order_desc,
    'amount' => $callbackData['amount'] ?? $transaction->amount,
    'currency' => $callbackData['currency'] ?? $transaction->currency,
    'error_message' => $callbackData['error_message'] ?? $transaction->error_message,
    'error_code' => $callbackData['error_code'] ?? $transaction->error_code,
    'initial_amount' => $callbackData['initial_amount'] ?? $transaction->initial_amount,
    'total_refunded_amount' => $callbackData['total_refunded_amount'] ?? $transaction->total_refunded_amount,
    'customer_id' => $callbackData['customer_id'] ?? $transaction->customer_id,
    'requisites' => $callbackData['requisites'] ?? $transaction->requisites,
    'bin_data' => $callbackData['bin_data'] ?? $transaction->bin_data,
    'merchant_id' => $merchantId ?? $transaction->merchant_id,
    'merchant_custom_data' => !empty($merchantCustomData) ? $merchantCustomData : $transaction->merchant_custom_data,
    'finished_at' => isset($callbackData['finished_at']) ? \Carbon\Carbon::parse($callbackData['finished_at']) : $transaction->finished_at,
];

// Update transaction
$transaction->update($updateData);
```

## Logging

When a transaction is updated from a callback, it's logged:

- **Transaction Log**: `storage/logs/transactions/transactions-{date}.log`
- **Callback Log**: `storage/logs/callbacks/callbacks-{date}.log`

The log includes:
- Transaction ID
- Order ID
- Old status → New status
- Source: 'callback'
- User ID

## Verification

To verify database updates after a callback:

```sql
SELECT 
    id,
    merchant_order_id,
    order_id,
    status,
    error_message,
    error_code,
    finished_at,
    updated_at
FROM transactions
WHERE merchant_order_id = 'your-order-id'
ORDER BY updated_at DESC;
```

Or check the transaction in the application:
- User Dashboard → Transactions → View Transaction
- Admin Dashboard → Transactions → View Transaction

