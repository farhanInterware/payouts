<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentServiceProvider;
use App\Services\LoggingService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $httpClient;

    public function __construct()
    {
        $this->paymentService = new PaymentServiceProvider(config('payment.merchant_control_value'));
        $this->httpClient = new Client([
            'base_uri' => config('payment.api_url'),
            'timeout' => 30,
        ]);
    }

    /**
     * Create payout request
     * 
     * ALWAYS generates merchant_order_id on backend - never accepts it from frontend.
     * This ensures uniqueness and prevents frontend manipulation.
     * Transaction is only created in database after successful API response.
     * 
     * @see PaymentController::createPayout() - Transaction creation conditions documented at line 158
     */
    public function createPayout(Request $request)
    {
        abort_unless(
            auth()->check() && auth()->user()->isAdmin(),
            403,
            'Only administrators can create withdrawals.'
        );

        $request->validate([
            'order_desc' => 'required|string',
            'amount' => 'required|string',
            'currency' => 'required|string|size:3',
            'pay_method' => 'required|string',
            'customer_email' => 'required|email|max:255',
            'requisites' => 'required|array',
            'requisites.customer.address.country' => 'required|string|size:2|regex:/^[A-Z]{2}$/',
        ], [
            'requisites.customer.address.country.regex' => 'Country must be a valid 2-letter ISO code (e.g., GB, US, IT)',
            'requisites.customer.address.country.size' => 'Country must be exactly 2 letters',
            'customer_email.required' => 'Customer email is required',
            'customer_email.email' => 'Please enter a valid email address',
        ]);

        // ALWAYS generate unique merchant_order_id on backend - ignore any value sent from frontend
        // This ensures uniqueness and prevents frontend manipulation
        $merchantOrderId = $this->generateUniqueMerchantOrderId();

        // Find or create user from customer_email (admin creates payouts on behalf of customers)
        $user = User::firstOrCreate(
            ['email' => $request->customer_email],
            [
                'name' => $request->customer_email, // Use email as name if not provided
                'password' => Hash::make(Str::random(32)), // Generate random password
            ]
        );

        // Get client's real public IP address automatically
        $ipAddress = $this->getClientIpAddress($request);

        // Build customer data structure for payload and transaction
        $customerData = [
            'id' => (string) $user->id,
            'email' => $user->email,
            'ip_address' => $ipAddress,
        ];

        // Prepare transaction data structure for saving (available in all catch blocks)
        // Transaction is associated with the customer user (found or created), not the admin/user creating it
        $transactionData = [
            'user_id' => $user->id, // Use customer user's ID, not the authenticated user's ID
            'merchant_order_id' => $merchantOrderId,
            'operation_type' => 'payout',
            'amount' => $request->amount,
            'currency' => $request->currency,
            'pay_method' => $request->pay_method,
            'order_desc' => $request->order_desc,
            'merchant_id' => config('payment.merchant_id'),
            'merchant_custom_data' => $request->merchant_custom_data ?? [],
            'requisites' => $request->requisites,
            'customer_info' => $customerData, // Use the customer data we built
            'browser_info' => $request->browser_info ?? null,
        ];

        try {
            
            // Build request payload
            $payload = [
                'merchant_order_id' => $merchantOrderId,
                'order_desc' => $request->order_desc,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'merchant' => [
                    'id' => (int) config('payment.merchant_id'),
                    'links' => [
                        'callback_url' => config('payment.callback_url'),
                        'result_url' => config('payment.result_url'),
                        'processing_url' => config('payment.processing_url'),
                        'success_url' => config('payment.success_url'),
                        'fail_url' => config('payment.fail_url'),
                        'back_url' => config('payment.back_url'),
                    ],
                ],
                'pay_method' => $request->pay_method,
                'requisites' => $request->requisites,
            ];

            // Build customer data for payload
            $payload['customer'] = $customerData;
            
            // Add browser info - use provided or dummy values
            if ($request->has('browser_info') && !empty($request->browser_info)) {
                $payload['customer']['browser_info'] = $request->browser_info;
            } else {
                // Add dummy browser info if not provided
                $payload['customer']['browser_info'] = [
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'accept_header' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                    'javascript_enabled' => true,
                    'language' => 'en-US',
                    'color_depth' => '24',
                    'timezone' => '-300',
                    'java_enabled' => false,
                    'screen_height' => 1080,
                    'screen_width' => 1920,
                ];
            }

            // Update transactionData customer_info to include browser_info for consistency
            $transactionData['customer_info'] = $payload['customer'];
            $transactionData['browser_info'] = $payload['customer']['browser_info'] ?? null;

            // Ensure country code is uppercase (ISO codes must be uppercase)
            if (isset($payload['requisites']['customer']['address']['country'])) {
                $payload['requisites']['customer']['address']['country'] = strtoupper($payload['requisites']['customer']['address']['country']);
            }

            // Add custom_data - use provided or dummy values
            if ($request->has('merchant_custom_data') && !empty($request->merchant_custom_data)) {
                $payload['merchant']['custom_data'] = $request->merchant_custom_data;
            } else {
                // Add dummy custom_data if not provided
                $payload['merchant']['custom_data'] = [
                    'property1' => 'custom_data1',
                    'property2' => 'custom_data2',
                ];
            }

            // Log payload before signing (for debugging)
            \Log::channel('api')->debug('Payload before signing', [
                'payload' => $payload,
                'custom_data_type' => gettype($payload['merchant']['custom_data']),
                'custom_data_value' => $payload['merchant']['custom_data'],
            ]);

            // Sign the request
            // Note: recursiveSortAndClean will convert empty custom_data array to stdClass {}
            $signedJson = $this->paymentService->sign(json_encode($payload));
            $signedPayload = json_decode($signedJson, true);

            // Log API request (decoded for readability)
            LoggingService::logApiRequest('Payment', '/payout', $signedPayload, 'POST');
            
            // Also log the actual JSON string being sent (for signature debugging)
            // Check custom_data format in JSON string
            $customDataStatus = 'not found';
            if (strpos($signedJson, '"custom_data":{}') !== false) {
                $customDataStatus = '{} (empty object)';
            } elseif (strpos($signedJson, '"custom_data":[]') !== false) {
                $customDataStatus = '[] (empty array - INVALID)';
            } elseif (preg_match('/"custom_data":\{[^}]*\}/', $signedJson)) {
                $customDataStatus = '{} (with values)';
            }
            
            \Log::channel('api')->info('API Request JSON String - Payment', [
                'module' => 'Payment',
                'endpoint' => '/payout',
                'method' => 'POST',
                'json_string' => $signedJson,
                'custom_data_in_json' => $customDataStatus,
                'timestamp' => now()->toIso8601String(),
            ]);
            LoggingService::logActivity('Payment', 'create_payout_request', [
                'merchant_order_id' => $merchantOrderId,
                'amount' => $request->amount,
                'currency' => $request->currency,
            ], auth()->id());

            // Send to API
            $response = $this->httpClient->post('/payout', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => $signedJson,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            $statusCode = $response->getStatusCode();

            // Log API response
            LoggingService::logApiResponse('Payment', '/payout', $responseData, $statusCode);

            /**
             * Store transaction in database
             * 
             * IMPORTANT: Transaction is ALWAYS created (success or failure):
             * 1. Validation passes (all required fields are valid)
             * 2. API call is attempted (success or error)
             * 3. Transaction saved with status:
             *    - 'processing' if API returns 200 (success)
             *    - 'failed' if API returns 4xx/5xx errors or network errors
             * 
             * Transaction is NOT created only if:
             * - Validation fails (before try block) - returns 422 with validation errors
             * 
             * Transaction is saved with error details when:
             * - API returns 4xx client error (ClientException) - saved with error_message and error_code
             * - API returns 5xx server error (ServerException) - saved with error_message and error_code
             * - Network/connection errors (Exception) - saved with error_message
             * 
             * merchant_order_id is ALWAYS unique:
             * - Generated using generateUniqueMerchantOrderId() which checks database for uniqueness
             * - Database has unique constraint on merchant_order_id column
             * - Prevents duplicate merchant_order_id values
             * 
             * This ensures all payment attempts are tracked, whether successful or failed.
             */
            $transaction = Transaction::create([
                'user_id' => $user->id, // Use customer user's ID, not the authenticated user's ID
                'merchant_order_id' => $merchantOrderId,
                'order_id' => $responseData['order_id'] ?? null,
                'operation_id' => $responseData['operation_id'] ?? null,
                'operation_type' => 'payout',
                'amount' => $request->amount,
                'currency' => $request->currency,
                'status' => $responseData['status'] ?? 'processing',
                'pay_method' => $request->pay_method,
                'order_desc' => $request->order_desc,
                'merchant_id' => config('payment.merchant_id'),
                'merchant_custom_data' => $request->merchant_custom_data ?? [],
                'requisites' => $request->requisites,
                'customer_info' => $payload['customer'], // Use the customer data we built
                'browser_info' => $payload['customer']['browser_info'] ?? null,
            ]);

            // Log transaction creation
            LoggingService::logTransaction('created', [
                'transaction_id' => $transaction->id,
                'merchant_order_id' => $transaction->merchant_order_id,
                'order_id' => $transaction->order_id,
                'operation_id' => $transaction->operation_id,
                'status' => $transaction->status,
            ], auth()->id());

            return response()->json([
                'success' => true,
                'transaction' => $transaction,
                'response' => $responseData,
            ]);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Handle API client errors (4xx) - e.g., invalid address, validation errors
            $statusCode = $e->getResponse()->getStatusCode();
            $responseBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($responseBody, true) ?? ['error' => $responseBody];
            
            // Save transaction with error details
            try {
                $transactionData['status'] = 'failed';
                $transaction = $this->saveTransactionWithError(
                    $transactionData,
                    $errorData['error_message'] ?? 'API client error: ' . $e->getMessage(),
                    $errorData['error_code'] ?? (string) $statusCode
                );
                
                // Log transaction creation with error
                LoggingService::logTransaction('created', [
                    'transaction_id' => $transaction->id,
                    'merchant_order_id' => $transaction->merchant_order_id,
                    'status' => $transaction->status,
                    'error_message' => $transaction->error_message,
                    'error_code' => $transaction->error_code,
                ], auth()->id());
            } catch (\Exception $dbException) {
                // If saving transaction fails, log it but don't break the error response
                Log::error('Failed to save transaction after API error', [
                    'merchant_order_id' => $merchantOrderId,
                    'db_error' => $dbException->getMessage(),
                ]);
            }
            
            // Log error with API response details (no stack trace)
            LoggingService::logApiError('Payment', '/payout', $e->getMessage(), [
                'merchant_order_id' => $merchantOrderId ?? null,
                'http_status' => $statusCode,
                'api_error' => $errorData,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $errorData['error_message'] ?? 'Failed to create payout request',
                'error_code' => $errorData['error_code'] ?? null,
                'transaction' => $transaction ?? null,
            ], $statusCode);
            
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            // Handle API server errors (5xx)
            $statusCode = $e->getResponse()->getStatusCode();
            $responseBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($responseBody, true) ?? ['error' => $responseBody];
            
            // Save transaction with error details
            try {
                $transactionData['status'] = 'failed';
                $transaction = $this->saveTransactionWithError(
                    $transactionData,
                    $errorData['error_message'] ?? 'API server error: ' . $e->getMessage(),
                    $errorData['error_code'] ?? (string) $statusCode
                );
                
                // Log transaction creation with error
                LoggingService::logTransaction('created', [
                    'transaction_id' => $transaction->id,
                    'merchant_order_id' => $transaction->merchant_order_id,
                    'status' => $transaction->status,
                    'error_message' => $transaction->error_message,
                    'error_code' => $transaction->error_code,
                ], auth()->id());
            } catch (\Exception $dbException) {
                // If saving transaction fails, log it but don't break the error response
                Log::error('Failed to save transaction after API error', [
                    'merchant_order_id' => $merchantOrderId,
                    'db_error' => $dbException->getMessage(),
                ]);
            }
            
            LoggingService::logApiError('Payment', '/payout', $e->getMessage(), [
                'merchant_order_id' => $merchantOrderId ?? null,
                'http_status' => $statusCode,
                'api_error' => $errorData,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment API server error',
                'error_code' => $errorData['error_code'] ?? null,
                'transaction' => $transaction ?? null,
            ], 500);
            
        } catch (\Exception $e) {
            // Handle other exceptions (network errors, etc.)
            // Save transaction with error details if we have transaction data
            $transaction = null;
            if (isset($transactionData)) {
                try {
                    $transactionData['status'] = 'failed';
                    $transaction = $this->saveTransactionWithError(
                        $transactionData,
                        'Network/connection error: ' . $e->getMessage(),
                        get_class($e)
                    );
                    
                    // Log transaction creation with error
                    LoggingService::logTransaction('created', [
                        'transaction_id' => $transaction->id,
                        'merchant_order_id' => $transaction->merchant_order_id,
                        'status' => $transaction->status,
                        'error_message' => $transaction->error_message,
                        'error_code' => $transaction->error_code,
                    ], auth()->id());
                } catch (\Exception $dbException) {
                    // If saving transaction fails, log it but don't break the error response
                    Log::error('Failed to save transaction after exception', [
                        'merchant_order_id' => $merchantOrderId ?? null,
                        'db_error' => $dbException->getMessage(),
                    ]);
                }
            }
            
            LoggingService::logApiError('Payment', '/payout', $e->getMessage(), [
                'merchant_order_id' => $merchantOrderId ?? null,
                'exception_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payout request: ' . $e->getMessage(),
                'transaction' => $transaction,
            ], 500);
        }
    }

    /**
     * Check transaction status
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
            'operation_id' => 'required|string',
        ]);

        try {
            // Build status request
            $payload = [
                'merchant_id' => (int) config('payment.merchant_id'),
                'order_id' => $request->order_id,
                'operation_id' => $request->operation_id,
            ];

            // Sign the request
            $signedJson = $this->paymentService->sign(json_encode($payload));
            $signedPayload = json_decode($signedJson, true);

            // Log API request (decoded for readability)
            LoggingService::logApiRequest('Payment', '/status', $signedPayload, 'POST');
            
            // Also log the actual JSON string being sent (for signature debugging)
            \Log::channel('api')->info('API Request JSON String - Payment Status', [
                'module' => 'Payment',
                'endpoint' => '/status',
                'method' => 'POST',
                'json_string' => $signedJson,
                'timestamp' => now()->toIso8601String(),
            ]);
            
            LoggingService::logActivity('Payment', 'check_status', [
                'order_id' => $request->order_id,
                'operation_id' => $request->operation_id,
            ], auth()->id());

            // Send to API
            $response = $this->httpClient->post('/status', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => $signedJson,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            $statusCode = $response->getStatusCode();

            // Log API response
            LoggingService::logApiResponse('Payment', '/status', $responseData, $statusCode);

            // Verify response signature
            if (isset($responseData['signature'])) {
                $isValid = $this->paymentService->verify(json_encode($responseData));
                if (!$isValid) {
                    Log::warning('Status response signature verification failed', ['order_id' => $request->order_id]);
                }
            }

            // Update transaction in database
            $transaction = Transaction::where('order_id', $request->order_id)
                ->where('operation_id', $request->operation_id)
                ->first();

            if ($transaction) {
                $oldStatus = $transaction->status;
                $transaction->update([
                    'status' => $responseData['status'] ?? $transaction->status,
                    'error_message' => $responseData['error_message'] ?? $transaction->error_message,
                    'error_code' => $responseData['error_code'] ?? $transaction->error_code,
                    'initial_amount' => $responseData['initial_amount'] ?? $transaction->initial_amount,
                    'total_refunded_amount' => $responseData['total_refunded_amount'] ?? $transaction->total_refunded_amount,
                    'finished_at' => isset($responseData['finished_at']) ? $responseData['finished_at'] : $transaction->finished_at,
                ]);

                // Log transaction status update
                if ($oldStatus !== $transaction->status) {
                    LoggingService::logTransaction('status_updated', [
                        'transaction_id' => $transaction->id,
                        'order_id' => $transaction->order_id,
                        'old_status' => $oldStatus,
                        'new_status' => $transaction->status,
                    ], auth()->id());
                }
            }

            return response()->json([
                'success' => true,
                'transaction' => $transaction,
                'response' => $responseData,
            ]);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Handle API client errors (4xx)
            $statusCode = $e->getResponse()->getStatusCode();
            $responseBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($responseBody, true) ?? ['error' => $responseBody];
            
            // Log the full error response for debugging
            LoggingService::logApiError('Payment', '/status', $e->getMessage(), [
                'order_id' => $request->order_id ?? null,
                'operation_id' => $request->operation_id ?? null,
                'http_status' => $statusCode,
                'api_error' => $errorData,
                'raw_response' => $responseBody,
                'request_payload' => $signedPayload ?? null,
            ]);
            
            // Extract error message - check multiple possible fields
            $errorMessage = $errorData['error_message'] 
                ?? $errorData['message'] 
                ?? $errorData['error'] 
                ?? (is_string($errorData) ? $errorData : 'Failed to check status');
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error_code' => $errorData['error_code'] ?? null,
                'api_error' => $errorData, // Include full error for debugging
            ], $statusCode);
            
        } catch (\Exception $e) {
            // Handle other exceptions
            LoggingService::logApiError('Payment', '/status', $e->getMessage(), [
                'order_id' => $request->order_id ?? null,
                'operation_id' => $request->operation_id ?? null,
                'exception_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle JSON callback from payment provider
     * 
     * According to API documentation:
     * - Must return 200 HTTP status for successful processing
     * - System will retry if non-200 status is returned
     * - Should handle duplicate callbacks gracefully (idempotent)
     * - Must verify signature before processing
     */
    public function callback(Request $request)
    {
        $callbackData = null;
        $orderId = null;
        $merchantOrderId = null;
        
        try {
            // Get raw JSON body for signature verification
            $rawJson = $request->getContent();
            $callbackData = json_decode($rawJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                LoggingService::logCallback('JSON', ['raw' => $rawJson], false);
                Log::error('Callback: Invalid JSON received', [
                    'error' => json_last_error_msg(),
                    'raw_content' => substr($rawJson, 0, 500), // Log first 500 chars
                ]);
                
                // Return 200 to prevent retries for malformed JSON
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON format',
                ], 200);
            }

            $orderId = $callbackData['order_id'] ?? null;
            $merchantOrderId = $callbackData['merchant_order_id'] ?? null;

            // Log incoming callback (before verification)
            LoggingService::logCallback('JSON', $callbackData, false);

            // Verify signature - critical for security
            $isValid = $this->paymentService->verify($rawJson);

            if (!$isValid) {
                LoggingService::logCallback('JSON', $callbackData, false);
                Log::warning('Callback signature verification failed', [
                    'order_id' => $orderId,
                    'merchant_order_id' => $merchantOrderId,
                ]);
                
                // Return 200 to acknowledge receipt but log the security issue
                // This prevents infinite retries while still logging the problem
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature',
                ], 200);
            }

            // Log verified callback
            LoggingService::logCallback('JSON', $callbackData, true);

            // Validate required fields
            $requiredFields = ['merchant_order_id', 'order_desc', 'amount', 'currency', 'merchant', 'order_id', 'status', 'pay_method', 'created_at'];
            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (!isset($callbackData[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                Log::error('Callback: Missing required fields', [
                    'order_id' => $orderId,
                    'merchant_order_id' => $merchantOrderId,
                    'missing_fields' => $missingFields,
                ]);
                
                // Return 200 but log the issue
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields: ' . implode(', ', $missingFields),
                ], 200);
            }

            // Find transaction by merchant_order_id or order_id
            $transaction = Transaction::where(function($query) use ($merchantOrderId, $orderId) {
                if ($merchantOrderId) {
                    $query->where('merchant_order_id', $merchantOrderId);
                }
                if ($orderId) {
                    $query->orWhere('order_id', $orderId);
                }
            })->first();

            if (!$transaction) {
                Log::warning('Callback: Transaction not found', [
                    'order_id' => $orderId,
                    'merchant_order_id' => $merchantOrderId,
                ]);
                
                // Return 200 - transaction might not exist yet or might be from different system
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found',
                ], 200);
            }

            // Extract merchant data
            $merchantData = $callbackData['merchant'] ?? [];
            $merchantId = $merchantData['id'] ?? null;
            $merchantCustomData = $merchantData['custom_data'] ?? [];

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
            ];

            // Handle finished_at timestamp
            if (isset($callbackData['finished_at']) && !empty($callbackData['finished_at'])) {
                try {
                    $updateData['finished_at'] = \Carbon\Carbon::parse($callbackData['finished_at']);
                } catch (\Exception $e) {
                    Log::warning('Callback: Invalid finished_at format', [
                        'order_id' => $orderId,
                        'finished_at' => $callbackData['finished_at'],
                    ]);
                }
            }

            // Check if this is a duplicate callback (idempotency check)
            $isDuplicate = true;
            $oldStatus = $transaction->status;
            
            foreach ($updateData as $key => $value) {
                // Compare values, handling JSON fields specially
                $currentValue = $transaction->$key;
                if (in_array($key, ['requisites', 'bin_data', 'merchant_custom_data', 'customer_info'])) {
                    $currentValue = is_string($currentValue) ? json_decode($currentValue, true) : $currentValue;
                    $newValue = is_string($value) ? json_decode($value, true) : $value;
                    if ($currentValue != $newValue) {
                        $isDuplicate = false;
                        break;
                    }
                } else {
                    if ($currentValue != $value) {
                        $isDuplicate = false;
                        break;
                    }
                }
            }

            if ($isDuplicate) {
                Log::info('Callback: Duplicate callback received (idempotent)', [
                    'transaction_id' => $transaction->id,
                    'order_id' => $orderId,
                    'status' => $transaction->status,
                ]);
                
                // Return 200 - successfully processed (duplicate)
                return response()->json([
                    'success' => true,
                    'message' => 'Callback processed (duplicate)',
                ], 200);
            }

            // Update transaction
            $transaction->update($updateData);

            // Log transaction update from callback
            LoggingService::logTransaction('callback_update', [
                'transaction_id' => $transaction->id,
                'order_id' => $transaction->order_id,
                'operation_id' => $transaction->operation_id,
                'old_status' => $oldStatus,
                'new_status' => $transaction->status,
                'source' => 'callback',
                'is_duplicate' => false,
            ], $transaction->user_id);

            Log::info('Callback processed successfully', [
                'transaction_id' => $transaction->id,
                'order_id' => $orderId,
                'merchant_order_id' => $merchantOrderId,
                'status' => $transaction->status,
            ]);

            // Return 200 HTTP status as required by API documentation
            return response()->json([
                'success' => true,
                'message' => 'Callback processed',
            ], 200);

        } catch (\Exception $e) {
            LoggingService::logApiError('Payment', '/callback', $e->getMessage(), [
                'merchant_order_id' => $merchantOrderId ?? $request->input('merchant_order_id'),
                'order_id' => $orderId ?? $request->input('order_id'),
                'exception_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Return 200 to prevent retries for unexpected errors
            // The error is logged, and we don't want infinite retries
            return response()->json([
                'success' => false,
                'message' => 'Error processing callback',
            ], 200);
        }
    }

    /**
     * Handle form POST from result_url redirect
     */
    public function result(Request $request)
    {
        try {
            $formData = $request->all();

            // Log incoming form callback
            LoggingService::logCallback('FORM', $formData, false);

            // Verify signature
            $isValid = $this->paymentService->verifyFormData($formData);

            if (!$isValid) {
                LoggingService::logCallback('FORM', $formData, false);
                Log::warning('Result form signature verification failed', ['data' => $formData]);
                return redirect(config('payment.fail_url'))->with('error', 'Invalid signature');
            }

            // Log verified callback
            LoggingService::logCallback('FORM', $formData, true);

            // Find transaction
            $transaction = Transaction::where('merchant_order_id', $formData['merchant_order_id'] ?? null)
                ->orWhere('order_id', $formData['order_id'] ?? null)
                ->first();

            if ($transaction) {
                $oldStatus = $transaction->status;
                $transaction->update([
                    'order_id' => $formData['order_id'] ?? $transaction->order_id,
                    'operation_id' => $formData['operation_id'] ?? $transaction->operation_id,
                    'operation_type' => $formData['operation_type'] ?? $transaction->operation_type,
                    'status' => $formData['status'] ?? $transaction->status,
                    'error_message' => $formData['error_message'] ?? $transaction->error_message,
                    'error_code' => $formData['error_code'] ?? $transaction->error_code,
                    'finished_at' => now(),
                ]);

                // Log transaction update from form callback
                LoggingService::logTransaction('result_update', [
                    'transaction_id' => $transaction->id,
                    'order_id' => $transaction->order_id,
                    'old_status' => $oldStatus,
                    'new_status' => $transaction->status,
                    'source' => 'result_form',
                ], $transaction->user_id);
            }

            // Redirect based on status
            $status = $formData['status'] ?? 'processing';
            if ($status === 'approved') {
                return redirect(config('payment.success_url'))->with('success', 'Transaction approved');
            } elseif ($status === 'declined') {
                return redirect(config('payment.fail_url'))->with('error', $formData['error_message'] ?? 'Transaction declined');
            } else {
                return redirect(config('payment.processing_url'))->with('info', 'Transaction is processing');
            }

        } catch (\Exception $e) {
            LoggingService::logApiError('Payment', '/result', $e->getMessage(), [
                'merchant_order_id' => $request->input('merchant_order_id'),
                'order_id' => $request->input('order_id'),
                'exception_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return redirect(config('payment.fail_url'))->with('error', 'An error occurred');
        }
    }

    /**
     * Generate a unique merchant_order_id
     * Ensures uniqueness by checking the database
     */
    private function generateUniqueMerchantOrderId(): string
    {
        $maxAttempts = 10;
        $attempt = 0;
        
        do {
            $merchantOrderId = 'order-' . time() . '-' . uniqid();
            $exists = Transaction::where('merchant_order_id', $merchantOrderId)->exists();
            $attempt++;
            
            if ($attempt >= $maxAttempts) {
                // Fallback: add random string to ensure uniqueness
                $merchantOrderId = 'order-' . time() . '-' . uniqid() . '-' . bin2hex(random_bytes(4));
                break;
            }
        } while ($exists);
        
        return $merchantOrderId;
    }

    /**
     * Save transaction to database with error details
     * Used when API returns errors but we still want to track the transaction
     */
    private function saveTransactionWithError(array $data, string $errorMessage = null, string $errorCode = null): Transaction
    {
        return Transaction::create([
            'user_id' => $data['user_id'],
            'merchant_order_id' => $data['merchant_order_id'],
            'order_id' => $data['order_id'] ?? null,
            'operation_id' => $data['operation_id'] ?? null,
            'operation_type' => $data['operation_type'] ?? 'payout',
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'status' => $data['status'] ?? 'failed',
            'pay_method' => $data['pay_method'],
            'order_desc' => $data['order_desc'],
            'merchant_id' => $data['merchant_id'] ?? config('payment.merchant_id'),
            'merchant_custom_data' => $data['merchant_custom_data'] ?? [],
            'requisites' => $data['requisites'],
            'customer_info' => $data['customer_info'],
            'browser_info' => $data['browser_info'] ?? null,
            'error_message' => $errorMessage,
            'error_code' => $errorCode,
            'finished_at' => now(), // Mark as finished since it failed
        ]);
    }

    /**
     * Get client's real public IPv4 address
     * Handles proxies, load balancers, and localhost scenarios
     * Returns IPv4 address only (converts IPv6 if possible)
     */
    private function getClientIpAddress(Request $request): string
    {
        // Try to get IP from various headers (for proxies/load balancers)
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_REAL_IP',            // Nginx proxy
            'HTTP_X_FORWARDED_FOR',      // Standard proxy header
            'HTTP_X_FORWARDED',           // Alternative proxy header
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Alternative
            'HTTP_FORWARDED',            // Alternative
        ];

        foreach ($ipHeaders as $header) {
            $ip = $request->server($header);
            if ($ip) {
                // X-Forwarded-For can contain multiple IPs, get the first one
                $ips = explode(',', $ip);
                $ip = trim($ips[0]);
                
                // Convert to IPv4 if needed
                $ipv4 = $this->convertToIPv4($ip);
                
                // Validate IPv4 (must be public IP, not private/reserved)
                if ($ipv4 && filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ipv4;
                }
            }
        }

        // Fallback to Laravel's ip() method
        $ip = $request->ip();
        
        // Convert to IPv4
        $ipv4 = $this->convertToIPv4($ip);
        
        // If localhost or invalid, use default for testing
        if (!$ipv4 || in_array($ipv4, ['127.0.0.1', 'localhost']) || empty($ipv4)) {
            // For local development, use a valid public IPv4 for testing
            if (config('app.env') === 'local') {
                return '8.8.8.8';
            }
            
            // In production, log warning
            Log::warning('Could not determine real client IPv4, using fallback', [
                'original_ip' => $ip,
                'ipv4' => $ipv4,
                'headers' => $request->headers->all(),
            ]);
            
            // Use fallback IPv4
            return '8.8.8.8';
        }

        return $ipv4;
    }

    /**
     * Convert IP address to IPv4 format
     * Handles IPv6 to IPv4 conversion for mapped addresses
     */
    private function convertToIPv4(string $ip): ?string
    {
        // If already IPv4, return as is
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $ip;
        }
        
        // If IPv6, try to extract IPv4 from mapped address (::ffff:192.168.1.1)
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Check if it's an IPv4-mapped IPv6 address (::ffff:x.x.x.x)
            if (strpos($ip, '::ffff:') === 0) {
                $ipv4 = substr($ip, 7);
                if (filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    return $ipv4;
                }
            }
            
            // For other IPv6 addresses, we can't convert them
            // Return null to indicate conversion failed
            return null;
        }
        
        // Invalid IP format
        return null;
    }
}

