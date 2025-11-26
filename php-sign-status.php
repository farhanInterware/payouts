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
    // Usage for status request:
    $provider = new PaymentServiceProvider('1ecacb670059bcb7943a9f67da5ccfee441edbc3');
    $signedJson = $provider->sign('{
      "merchant_id": 85,
      "order_id": "ca191f46-328e-49b9-bf11-2e4e0603a590",
      "operation_id": "fa19aa43-ca52-4967-ba30-f4ad9c810d7f"
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

