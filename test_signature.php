<?php

require __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Use the same PaymentServiceProvider from Laravel
require __DIR__ . '/app/Services/PaymentServiceProvider.php';

use App\Services\PaymentServiceProvider;

$merchantControlValue = env('PAYMENT_MERCHANT_CONTROL_VALUE', '1ecacb670059bcb7943a9f67da5ccfee441edbc3');
$provider = new PaymentServiceProvider($merchantControlValue);

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
        'iban' => 'IT60X0542811101000000123456',
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

echo "=== SIGNING ===\n";
$signedJson = $provider->sign(json_encode($payload));

echo "=== SIGNED JSON ===\n";
echo $signedJson . "\n\n";

$signedArray = json_decode($signedJson, true);
echo "=== SIGNED JSON (DECODED) ===\n";
echo json_encode($signedArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

echo "=== SIGNATURE ===\n";
echo $signedArray['signature'] . "\n\n";

// Check if custom_data is {} or []
$decoded = json_decode($signedJson);
echo "=== CUSTOM_DATA TYPE ===\n";
echo "Type: " . gettype($decoded->merchant->custom_data) . "\n";
echo "Is object: " . (is_object($decoded->merchant->custom_data) ? 'YES' : 'NO') . "\n";
echo "Is array: " . (is_array($decoded->merchant->custom_data) ? 'YES' : 'NO') . "\n";
echo "JSON representation: " . json_encode($decoded->merchant->custom_data) . "\n";

