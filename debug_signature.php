<?php

// Standalone signature debug script
// This mimics the PaymentServiceProvider logic to debug signature calculation

$merchantControlValue = '1ecacb670059bcb7943a9f67da5ccfee441edbc3';

function recursiveSortAndClean(array $input): array
{
    if (array_keys($input) !== range(0, count($input) - 1)) {
        ksort($input);
    }
    foreach ($input as $key => $value) {
        if (is_array($value)) {
            // If it's an empty array and the key is 'custom_data', convert to stdClass
            // so it encodes as {} in JSON, not []
            if (empty($value) && $key === 'custom_data') {
                $input[$key] = new \stdClass();
            } else {
                $input[$key] = recursiveSortAndClean($value);
            }
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

function generateSHA256(string $input): string
{
    return hash('sha256', $input);
}

function sign(string $json, string $merchantControlValue): string
{
    $requestBody = json_decode($json, true);
    if (!is_array($requestBody)) {
        throw new RuntimeException("Failed to decode the JSON string.");
    }

    echo "=== STEP 1: After json_decode ===\n";
    echo "custom_data type: " . gettype($requestBody['merchant']['custom_data']) . "\n";
    echo "custom_data value: " . json_encode($requestBody['merchant']['custom_data']) . "\n\n";

    $sortedAndCleanedBody = recursiveSortAndClean($requestBody);
    
    echo "=== STEP 2: After recursiveSortAndClean ===\n";
    echo "custom_data type: " . gettype($sortedAndCleanedBody['merchant']['custom_data']) . "\n";
    echo "custom_data value: " . json_encode($sortedAndCleanedBody['merchant']['custom_data']) . "\n";
    echo "Is stdClass: " . (is_object($sortedAndCleanedBody['merchant']['custom_data']) ? 'YES' : 'NO') . "\n\n";

    $finalBody = array_filter($sortedAndCleanedBody, function ($value): bool {
        return !is_null($value);
    });

    echo "=== STEP 3: After array_filter ===\n";
    echo "custom_data type: " . gettype($finalBody['merchant']['custom_data']) . "\n";
    echo "custom_data value: " . json_encode($finalBody['merchant']['custom_data']) . "\n";
    echo "Is stdClass: " . (is_object($finalBody['merchant']['custom_data']) ? 'YES' : 'NO') . "\n\n";

    $bodyString = json_encode($finalBody, JSON_UNESCAPED_SLASHES);
    if ($bodyString === false) {
        throw new RuntimeException("Failed to encode the sorted and cleaned body to JSON.");
    }

    echo "=== STEP 4: JSON string for signature calculation ===\n";
    echo $bodyString . "\n\n";
    
    // Check if custom_data is {} or [] in the JSON string
    if (strpos($bodyString, '"custom_data":{}') !== false) {
        echo "✓ custom_data is {} in JSON string\n";
    } elseif (strpos($bodyString, '"custom_data":[]') !== false) {
        echo "✗ custom_data is [] in JSON string (WRONG!)\n";
    } else {
        echo "? custom_data format unclear\n";
    }
    echo "\n";

    $signedString = $bodyString . $merchantControlValue;
    $signature = generateSHA256($signedString);
    
    echo "=== STEP 5: Signature calculation ===\n";
    echo "String to hash: " . substr($signedString, 0, 200) . "...[merchant_control_value]\n";
    echo "Signature: " . $signature . "\n\n";
    
    // Add signature to finalBody
    $finalBody['signature'] = $signature;

    $finalJson = json_encode($finalBody, JSON_UNESCAPED_SLASHES);
    if ($finalJson === false) {
        throw new RuntimeException("Failed to encode the final body with signature to JSON.");
    }

    echo "=== STEP 6: Final JSON with signature ===\n";
    echo $finalJson . "\n\n";
    
    // Check if custom_data is {} or [] in the final JSON
    if (strpos($finalJson, '"custom_data":{}') !== false) {
        echo "✓ custom_data is {} in final JSON\n";
    } elseif (strpos($finalJson, '"custom_data":[]') !== false) {
        echo "✗ custom_data is [] in final JSON (WRONG!)\n";
    } else {
        echo "? custom_data format unclear\n";
    }

    return $finalJson;
}

// Recreate the exact request from the log
$payload = [
    'merchant_order_id' => 'order-1763380299',
    'order_desc' => 'Irure Nam aut conseq',
    'amount' => '2',
    'currency' => 'EUR',
    'merchant' => [
        'id' => 85,
        'links' => [
            'back_url' => '/',
            'callback_url' => '/api/payment/callback',
            'fail_url' => '/payment/fail',
            'processing_url' => '/payment/processing',
            'result_url' => '/payment/result',
            'success_url' => '/payment/success',
        ],
        'custom_data' => [], // Empty array - should become {}
    ],
    'customer' => [
        'browser_info' => [
            'accept_header' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'color_depth' => '24',
            'java_enabled' => false,
            'javascript_enabled' => true,
            'language' => 'en-US',
            'screen_height' => 768,
            'screen_width' => 1366,
            'timezone' => '-300',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',
        ],
        'email' => 'fasira@mailinator.com',
        'id' => '123',
        'ip_address' => '127.0.0.1',
    ],
    'pay_method' => 'sepa',
    'requisites' => [
        'account_name' => 'Evan Walls',
        'account_number' => '254',
        'customer' => [
            'address' => [
                'address1' => '687 Cowley Extension',
                'country' => 'IT',
            ],
            'first_name' => 'Glenna',
            'last_name' => 'Hardy',
        ],
    ],
];

echo "=== ORIGINAL PAYLOAD ===\n";
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

echo "=== EXPECTED SIGNATURE FROM LOG ===\n";
echo "892a5e8439c291e0310b2cdd6568734b7ad76f577c3d73f5d3519933ba68524b\n\n";

echo "=== CALCULATED SIGNATURE ===\n";
$signedJson = sign(json_encode($payload), $merchantControlValue);

$decoded = json_decode($signedJson, true);
echo "\n=== COMPARISON ===\n";
echo "Expected:  892a5e8439c291e0310b2cdd6568734b7ad76f577c3d73f5d3519933ba68524b\n";
echo "Calculated: " . $decoded['signature'] . "\n";
echo "Match: " . ($decoded['signature'] === '892a5e8439c291e0310b2cdd6568734b7ad76f577c3d73f5d3519933ba68524b' ? 'YES ✓' : 'NO ✗') . "\n";

