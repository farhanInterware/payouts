<?php

declare(strict_types=1);

class PaymentServiceProvider {

    private string $merchantControlValue;

    public function __construct(string $merchantControlValue) {
        $this->merchantControlValue = $merchantControlValue;
    }

    public function verify(string $json): bool {
        $callbackData = json_decode($json, true);
        if (!is_array($callbackData)) {
            throw new RuntimeException("Failed to decode the JSON string.");
        }

        // Extract the signature from callback (sent by payment provider)
        // We don't add the signature - we receive it and verify it
        if (!isset($callbackData['signature'])) {
            throw new RuntimeException("Signature not found in callback data.");
        }
        $receivedSignature = $callbackData['signature'];

        // Remove signature from data for verification (we calculate our own to compare)
        unset($callbackData['signature']);

        // Sort and clean the data (same process as signing)
        $sortedAndCleanedBody = $this->recursiveSortAndClean($callbackData);
        $finalBody = array_filter($sortedAndCleanedBody, function ($value): bool {
            return !is_null($value);
        });

        // Generate signature from cleaned data
        $bodyString = json_encode($finalBody, JSON_UNESCAPED_SLASHES);
        if ($bodyString === false) {
            throw new RuntimeException("Failed to encode the sorted and cleaned body to JSON.");
        }

        $signedString = $bodyString . $this->merchantControlValue;
        $calculatedSignature = $this->generateSHA256($signedString);

        // Compare signatures using timing-safe comparison
        return hash_equals($receivedSignature, $calculatedSignature);
    }

    private function recursiveSortAndClean(array $input): array {
        if (array_keys($input) !== range(0, count($input) - 1)) {
            ksort($input);
        }
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = $this->recursiveSortAndClean($value);
            } elseif (is_string($value)) {
                $trimmed = trim($value);
                if ($trimmed === "") {
                    $input[$key] = null;
                } else {
                    $input[$key] = $trimmed;
                }
            }
        }
        return $input;
    }

    private function generateSHA256(string $input): string {
        return hash('sha256', $input);
    }
}

// Set headers for JSON response
header('Content-Type: application/json; charset=utf-8');

try {
    // Get callback data from POST request (sent by payment provider)
    // The callback will include a signature field that we need to verify
    $callbackJson = file_get_contents('php://input');
    
    // If no POST data, use example callback for testing
    // Note: The signature in the example is what the payment provider sends to us
    if (empty($callbackJson)) {
        $callbackJson = '{
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
            },
            "account_number": "string",
            "account_name": "string",
            "bank": {
              "name": "string",
              "branch": "string",
              "code": "string",
              "bic": "string",
              "address": {
                "country": "GB",
                "state": "Il",
                "city": "London",
                "address1": "17 Main st",
                "zip_code": "554-65"
              }
            }
          },
          "bin_data": {
            "card_brand": "MASTERCARD",
            "country": "US",
            "bank_name": "Shazam, INC"
          }
        }';
    }

    // Verify the callback signature
    $provider = new PaymentServiceProvider('1ecacb670059bcb7943a9f67da5ccfee441edbc3');
    $isValid = $provider->verify($callbackJson);
    
    // Return verification result
    echo json_encode([
        'valid' => $isValid,
        'message' => $isValid ? 'Signature is valid' : 'Signature is invalid'
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Return error as JSON
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'valid' => false,
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}

?>

