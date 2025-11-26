<?php

/**
 * Callback Testing Script
 * 
 * This script helps you test the callback endpoint with various scenarios.
 * 
 * Usage:
 *   php test_callback.php [scenario]
 * 
 * Scenarios:
 *   - valid: Test with valid signature and existing transaction
 *   - invalid-signature: Test with invalid signature
 *   - missing-fields: Test with missing required fields
 *   - no-transaction: Test with non-existent transaction
 *   - duplicate: Test duplicate callback (idempotency)
 *   - approved: Test approved status callback
 *   - declined: Test declined status callback
 */

require __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PaymentServiceProvider;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Config;

// Get configuration
$merchantControlValue = Config::get('payment.merchant_control_value');
$merchantId = Config::get('payment.merchant_id');
$callbackUrl = Config::get('payment.callback_url');
$appUrl = Config::get('app.url');

// If callback URL is relative, make it absolute
if (strpos($callbackUrl, 'http') !== 0) {
    $callbackUrl = $appUrl . $callbackUrl;
}

$provider = new PaymentServiceProvider($merchantControlValue);

// Get scenario from command line or default to 'valid'
$scenario = $argv[1] ?? 'valid';

echo "=== Callback Testing Script ===\n";
echo "Scenario: {$scenario}\n";
echo "Callback URL: {$callbackUrl}\n\n";

// Get or create a test transaction
$user = User::first();
if (!$user) {
    echo "ERROR: No users found in database. Please create a user first.\n";
    exit(1);
}

// Find an existing transaction or create a test one
$transaction = Transaction::where('user_id', $user->id)->first();

if (!$transaction) {
    echo "Creating test transaction...\n";
    $transaction = Transaction::create([
        'user_id' => $user->id,
        'merchant_order_id' => 'test-order-' . time(),
        'order_id' => 'c' . bin2hex(random_bytes(4)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(6)),
        'operation_id' => 'f' . bin2hex(random_bytes(4)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(6)),
        'operation_type' => 'payout',
        'amount' => '100.00',
        'currency' => 'USD',
        'status' => 'processing',
        'pay_method' => 'card',
        'order_desc' => 'Test transaction for callback testing',
        'merchant_id' => $merchantId,
    ]);
    echo "Created transaction: {$transaction->merchant_order_id}\n";
} else {
    echo "Using existing transaction: {$transaction->merchant_order_id}\n";
}

// Build callback payload based on scenario
$callbackData = [
    'merchant_order_id' => $transaction->merchant_order_id,
    'order_desc' => $transaction->order_desc ?? 'Test callback',
    'amount' => (string) $transaction->amount,
    'currency' => $transaction->currency,
    'merchant' => [
        'id' => $merchantId,
        'custom_data' => new stdClass(),
    ],
    'order_id' => $transaction->order_id,
    'operation_id' => $transaction->operation_id ?? ('f' . bin2hex(random_bytes(4)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(6))),
    'operation_type' => $transaction->operation_type ?? 'payout',
    'status' => 'approved',
    'pay_method' => $transaction->pay_method ?? 'card',
    'created_at' => now()->format('Y-m-d\TH:i:sP'),
    'finished_at' => now()->format('Y-m-d\TH:i:sP'),
];

// Modify payload based on scenario
switch ($scenario) {
    case 'approved':
        $callbackData['status'] = 'approved';
        break;
        
    case 'declined':
        $callbackData['status'] = 'declined';
        $callbackData['error_message'] = 'Test decline reason';
        $callbackData['error_code'] = '5118';
        break;
        
    case 'processing':
        $callbackData['status'] = 'processing';
        unset($callbackData['finished_at']);
        break;
        
    case 'missing-fields':
        unset($callbackData['merchant_order_id']);
        unset($callbackData['order_desc']);
        break;
        
    case 'no-transaction':
        $callbackData['merchant_order_id'] = 'non-existent-order-' . time();
        $callbackData['order_id'] = 'c' . bin2hex(random_bytes(4)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(6));
        break;
        
    case 'invalid-signature':
        // Will be handled after signing
        break;
        
    case 'duplicate':
        // Use same data as previous call
        break;
        
    case 'valid':
    default:
        // Use default approved status
        break;
}

// Add optional fields
if (!isset($callbackData['error_message'])) {
    $callbackData['initial_amount'] = (string) $transaction->amount;
    $callbackData['total_refunded_amount'] = '0.00';
    $callbackData['customer_id'] = 'test-customer-123';
    $callbackData['requisites'] = [
        'card' => [
            'bin' => 456456,
            'last4' => '4321',
        ],
    ];
    $callbackData['bin_data'] = [
        'card_brand' => 'MASTERCARD',
        'country' => 'US',
        'bank_name' => 'Test Bank',
    ];
}

// Sign the callback
$jsonPayload = json_encode($callbackData, JSON_UNESCAPED_SLASHES);
$signedJson = $provider->sign($jsonPayload);
$signedData = json_decode($signedJson, true);

// Modify signature for invalid-signature scenario
if ($scenario === 'invalid-signature') {
    $signedData['signature'] = 'invalid_signature_' . bin2hex(random_bytes(16));
    $signedJson = json_encode($signedData, JSON_UNESCAPED_SLASHES);
}

echo "\n=== Callback Payload ===\n";
echo json_encode($signedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

// Test callback directly by calling the controller method
use Illuminate\Http\Request;
use App\Http\Controllers\PaymentController;

echo "Testing callback handler directly...\n";
$startTime = microtime(true);

try {
    // Create a request with the raw JSON string (as the callback expects)
    $request = Request::create('/api/payment/callback', 'POST', [], [], [], [], $signedJson);
    $request->headers->set('Content-Type', 'application/json');
    $request->headers->set('Accept', 'application/json');
    
    // Call the controller method directly
    $controller = app(PaymentController::class);
    $response = $controller->callback($request);
    
    $endTime = microtime(true);
    $httpCode = $response->getStatusCode();
    $responseBody = $response->getContent();
    $error = null;
} catch (\Exception $e) {
    $endTime = microtime(true);
    $httpCode = 500;
    $responseBody = json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
    $error = $e->getMessage();
}

echo "\n=== Response ===\n";
echo "HTTP Status: {$httpCode}\n";
echo "Response Time: " . round(($endTime - $startTime) * 1000, 2) . "ms\n";

if ($error) {
    echo "Error: {$error}\n";
}
echo "Response Body:\n";
$responseData = json_decode($responseBody, true);
if ($responseData) {
    echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} else {
    echo $responseBody . "\n";
}

// Check transaction status if it exists
if ($scenario !== 'no-transaction') {
    $transaction->refresh();
    echo "\n=== Transaction Status ===\n";
    echo "Status: {$transaction->status}\n";
    echo "Updated at: {$transaction->updated_at}\n";
    if ($transaction->finished_at) {
        echo "Finished at: {$transaction->finished_at}\n";
    }
}

echo "\n=== Test Complete ===\n";
echo "Check logs at: storage/logs/callbacks/callbacks-" . date('Y-m-d') . ".log\n";
echo "Check transaction logs at: storage/logs/transactions/transactions-" . date('Y-m-d') . ".log\n";

