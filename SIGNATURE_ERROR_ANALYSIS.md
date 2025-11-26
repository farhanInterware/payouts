# Signature Error Analysis

## Error Details
- **Error Code**: 5102
- **Error Message**: "Invalid signature"
- **HTTP Status**: 401 Unauthorized
- **API Endpoint**: `POST https://sandbox-api.debitly.tech/payout`

## What's Happening

The API is rejecting the request because the signature doesn't match what it expects. This happens during signature verification on the API side.

## Possible Causes

### 1. **Merchant Control Value Mismatch** (Most Likely)
The `PAYMENT_MERCHANT_CONTROL_VALUE` in your `.env` file might be incorrect or different from what the API expects.

**Check:**
- Verify `PAYMENT_MERCHANT_CONTROL_VALUE` in `.env` matches your API credentials
- Default value in code: `1ecacb670059bcb7943a9f67da5ccfee441edbc3`
- Make sure there are no extra spaces or quotes

### 2. **JSON Encoding Differences**
Even though our signature calculation appears correct, the API might be receiving the JSON in a slightly different format.

**What we've verified:**
- ✅ Signature calculation logic is correct (debug script confirms)
- ✅ `custom_data` is correctly converted to `{}` (object) not `[]` (array)
- ✅ JSON is properly sorted and cleaned before signature calculation

### 3. **Request Format Issues**
The API might expect:
- Different field ordering (we sort alphabetically)
- Different encoding (we use `JSON_UNESCAPED_SLASHES`)
- Different handling of empty values

## Debugging Steps

1. **Verify Merchant Control Value:**
   ```bash
   php artisan tinker
   >>> config('payment.merchant_control_value')
   ```

2. **Check the actual JSON being sent:**
   - Make a new request
   - Check `storage/logs/api/api-YYYY-MM-DD.log` for the "API Request JSON String" entry
   - Compare it with what the API expects

3. **Test with original PHP file:**
   - Use `php-sign-request.php` with the same data
   - See if it works (if it does, compare the signatures)

## Next Steps

1. **Verify your `.env` file:**
   - Check `PAYMENT_MERCHANT_CONTROL_VALUE` is correct
   - Check `PAYMENT_MERCHANT_ID` is correct (should be `85`)

2. **Make a new request:**
   - The new logging will show the exact JSON string being sent
   - Compare it with API documentation

3. **Contact API Support:**
   - If credentials are correct, the API might have specific requirements
   - Ask for example request/response or signature calculation details

## Files to Check

- `.env` - Verify `PAYMENT_MERCHANT_CONTROL_VALUE`
- `config/payment.php` - Verify configuration
- `storage/logs/api/api-*.log` - Check actual JSON being sent
- `debug_signature.php` - Test signature calculation independently

