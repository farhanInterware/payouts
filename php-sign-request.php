<?php

declare(strict_types=1);

class PaymentServiceProvider {

    private string $merchantControlValue;

    public function __construct(string $merchantControlValue) {
        $this->merchantControlValue = $merchantControlValue;
    }

    public function sign(string $json): string {
        $requestBody = json_decode($json, true);
        if (!is_array($requestBody)) {
            throw new RuntimeException("Failed to decode the JSON string.");
        }

        $sortedAndCleanedBody = $this->recursiveSortAndClean($requestBody);
        $finalBody = array_filter($sortedAndCleanedBody, function ($value): bool {
            return !is_null($value);
        });

        $bodyString = json_encode($finalBody, JSON_UNESCAPED_SLASHES);
        if ($bodyString === false) {
            throw new RuntimeException("Failed to encode the sorted and cleaned body to JSON.");
        }

        $signedString = $bodyString . $this->merchantControlValue;
        $signature = $this->generateSHA256($signedString);
        $finalBody['signature'] = $signature;

        $finalJson = json_encode($finalBody, JSON_UNESCAPED_SLASHES);
        if ($finalJson === false) {
            throw new RuntimeException("Failed to encode the final body with signature to JSON.");
        }

        return $finalJson;
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
    // Usage:
    $provider = new PaymentServiceProvider('1ecacb670059bcb7943a9f67da5ccfee441edbc3');
    $signedJson = $provider->sign('{
      "merchant_order_id": "order-1567",
      "order_desc": "Payment for online course subscription",
      "amount": "2",
      "currency": "EUR",
      "merchant": {
        "id": 85,
        "links": {
          "callback_url": "https://store.com/callback/order-1567",
          "result_url": "https://store.com/result/order-1567",
          "processing_url": "https://store.com/processing/order-1567",
          "success_url": "https://store.com/success/order-1567",
          "fail_url": "https://store.com/fail/order-1567",
          "back_url": "https://store.com/home"
        },
        "custom_data": {
          "property1": "string",
          "property2": "string"
        }
      },
      "customer": {
        "id": "cust-1729",
        "email": "john.doe@example.com",
        "ip_address": "192.168.1.101",
        "browser_info": {
          "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36",
          "accept_header": "text/html, application/xhtml+xml, application/xml;q=0.9, image/avif, image/webp, image/apng, */*;q=0.8",
          "javascript_enabled": true,
          "language": "en-US",
          "color_depth": "24",
          "timezone": "-300",
          "java_enabled": false,
          "screen_height": 1080,
          "screen_width": 1920
        }
      },
      "pay_method": "sepa",
      "requisites": {
        "customer": {
          "first_name": "Jonh",
          "last_name": "Webb",
          "address": {
            "country": "GB",
            "address1": "17 Main st"
          }
        },
        "account_number": "IT60X0542811101000000123456",
        "account_name": "Test Name"
      }
    }');
    
    // Return the signed JSON
    echo $signedJson;
    
} catch (Exception $e) {
    // Return error as JSON
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}

?>
