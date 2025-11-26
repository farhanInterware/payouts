# Callback Implementation Documentation

## Overview
This document describes the implementation of the merchant callback handler according to the Payment Gateway API documentation.

## Callback Endpoint
- **Route**: `/api/payment/callback`
- **Method**: `POST`
- **Content-Type**: `application/json`
- **Authentication**: None (called by payment provider)
- **CSRF Protection**: Disabled (configured in `VerifyCsrfToken` middleware)

## Key Features

### 1. Signature Verification
- All callbacks are verified using SHA256 signature
- Invalid signatures are logged but return 200 to prevent retries
- Signature verification uses the same algorithm as request signing

### 2. Idempotency Handling
- Duplicate callbacks are detected and handled gracefully
- If a callback with identical data is received, it's acknowledged without updating the database
- Prevents duplicate processing of the same callback

### 3. Required Fields Validation
The callback handler validates the following required fields:
- `merchant_order_id`
- `order_desc`
- `amount`
- `currency`
- `merchant` (object with `id` and `custom_data`)
- `order_id`
- `status`
- `pay_method`
- `created_at`

### 4. Data Storage
All callback fields are stored in the `transactions` table:
- **Basic Info**: `merchant_order_id`, `order_id`, `operation_id`, `operation_type`
- **Status**: `status`, `error_message`, `error_code`
- **Amounts**: `amount`, `currency`, `initial_amount`, `total_refunded_amount`
- **Payment Method**: `pay_method`
- **Customer**: `customer_id`
- **Merchant**: `merchant_id`, `merchant_custom_data`
- **Payment Details**: `requisites` (JSON), `bin_data` (JSON)
- **Timestamps**: `created_at`, `finished_at`

### 5. HTTP Response Handling
According to API documentation:
> "The system will consistently attempt redeliveries upon not receiving a 200 HTTP status until either a success is confirmed or the maximum attempt threshold is reached."

**Response Strategy**:
- **200 OK**: Returned for all processed callbacks (successful or not)
  - Prevents infinite retries for non-retryable errors
  - Errors are logged for investigation
  - Includes: successful processing, invalid signatures, missing transactions, malformed requests

**Why always return 200?**
- Invalid signatures shouldn't be retried (security issue)
- Missing transactions shouldn't be retried (transaction doesn't exist)
- Malformed requests shouldn't be retried (data issue)
- All errors are logged for manual investigation

### 6. Error Logging
All callback events are logged:
- **Incoming callbacks**: Logged before verification
- **Verified callbacks**: Logged after successful signature verification
- **Errors**: Comprehensive error logging with context
- **Transaction updates**: Logged when transaction status changes

### 7. Redelivery Mechanism
The payment gateway implements automatic redelivery with the following intervals:
- 15, 60, 300, 900, 900, 900, 3600, 3600, 14400, 86400 seconds
- Final interval (86400 seconds = 24 hours) is repeated 14 times
- Maximum retry period: ~15 days

**Our Implementation**:
- Returns 200 for all callbacks to prevent unnecessary retries
- Handles duplicate callbacks idempotently
- Logs all events for audit trail

## Callback Payload Example

```json
{
  "signature": "740e15e5ba066c37f47e322627175d351b00eacf0908d2ca05d3837c38f7c21b",
  "merchant_order_id": "order-12345",
  "order_desc": "Monthly subscription #43tt",
  "amount": "22.12",
  "currency": "USD",
  "merchant": {
    "id": 778393,
    "custom_data": {}
  },
  "order_id": "cf194f18-95c0-49ef-8fff-f496f31a071c",
  "operation_id": "ff094dca-5165-42e8-aa44-ddfc319d75cb",
  "operation_type": "payment",
  "status": "approved",
  "pay_method": "card",
  "created_at": "2021-06-01T18:47:27+0000",
  "finished_at": "2021-06-01T18:47:27+0000",
  "error_message": "General decline",
  "error_code": "5118",
  "initial_amount": "22.12",
  "total_refunded_amount": "22.12",
  "customer_id": "id762",
  "requisites": {
    "card": {
      "bin": 456456,
      "last4": "4321"
    }
  },
  "bin_data": {
    "card_brand": "MASTERCARD",
    "country": "US",
    "bank_name": "Shazam, INC"
  }
}
```

## Status Values
- `processing`: Operation is being processed
- `declined`: Operation was declined
- `approved`: Operation was approved
- `expired`: Operation expired

## Operation Types
- `payment`: Payment operation
- `payout`: Payout operation
- `refund`: Refund operation
- `preauth`: Pre-authorization
- `capture`: Capture operation
- `cancel`: Cancel operation

## Security Considerations

1. **Signature Verification**: All callbacks must have valid signatures
2. **CSRF Protection**: Disabled for callback endpoint (external service)
3. **Input Validation**: All required fields are validated
4. **Error Handling**: Errors are logged but don't expose sensitive information
5. **Idempotency**: Duplicate callbacks are handled safely

## Monitoring

Monitor the following logs:
- `storage/logs/callbacks/callbacks-{date}.log`: All callback events
- `storage/logs/transactions/transactions-{date}.log`: Transaction updates
- `storage/logs/api/api-{date}.log`: API errors

## Testing

To test the callback endpoint:
1. Use the payment gateway's test environment
2. Create a test transaction
3. Wait for callback or trigger manually (if supported)
4. Check logs for callback processing
5. Verify transaction status in database

## Troubleshooting

### Callback not received
- Check firewall/security settings
- Verify callback URL is accessible from payment gateway
- Check server logs for incoming requests

### Invalid signature errors
- Verify `PAYMENT_MERCHANT_CONTROL_VALUE` in `.env` matches payment gateway
- Check signature calculation algorithm
- Review callback payload structure

### Duplicate callbacks
- This is normal behavior (network delays, retries)
- System handles duplicates idempotently
- Check logs to see if duplicates are being processed correctly

### Transaction not found
- Callback might be for a transaction from a different system
- Check `merchant_order_id` and `order_id` in logs
- Verify transaction was created before callback

