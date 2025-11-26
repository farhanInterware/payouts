<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\PaymentServiceProvider;
use App\Services\LoggingService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
     */
    public function createPayout(Request $request)
    {
        $request->validate([
            'merchant_order_id' => 'required|string|max:128',
            'order_desc' => 'required|string',
            'amount' => 'required|string',
            'currency' => 'required|string|size:3',
            'pay_method' => 'required|string',
            'customer' => 'required|array',
            'requisites' => 'required|array',
            'requisites.customer.address.country' => 'required|string|size:2|regex:/^[A-Z]{2}$/',
        ], [
            'requisites.customer.address.country.regex' => 'Country must be a valid 2-letter ISO code (e.g., GB, US, IT)',
            'requisites.customer.address.country.size' => 'Country must be exactly 2 letters',
        ]);

        try {
            // Build request payload
            $payload = [
                'merchant_order_id' => $request->merchant_order_id,
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
                'customer' => $request->customer,
                'pay_method' => $request->pay_method,
                'requisites' => $request->requisites,
            ];

            // Add browser info if available
            if ($request->has('browser_info')) {
                $payload['customer']['browser_info'] = $request->browser_info;
            }

            // Ensure country code is uppercase (ISO codes must be uppercase)
            if (isset($payload['requisites']['customer']['address']['country'])) {
                $payload['requisites']['customer']['address']['country'] = strtoupper($payload['requisites']['customer']['address']['country']);
            }

            // Add custom_data - API requires it, use empty object if not provided
            if ($request->has('merchant_custom_data') && !empty($request->merchant_custom_data)) {
                $payload['merchant']['custom_data'] = $request->merchant_custom_data;
            } else {
                // API requires custom_data as object - use empty associative array
                // This will be JSON encoded as {} (object) not [] (array)
                $payload['merchant']['custom_data'] = [];
            }

            // Sign the request
            $signedJson = $this->paymentService->sign(json_encode($payload));
            $signedPayload = json_decode($signedJson, true);

            // Log API request (decoded for readability)
            LoggingService::logApiRequest('Payment', '/payout', $signedPayload, 'POST');
            
            // Also log the actual JSON string being sent (for signature debugging)
            \Log::channel('api')->info('API Request JSON String - Payment', [
                'module' => 'Payment',
                'endpoint' => '/payout',
                'method' => 'POST',
                'json_string' => $signedJson,
                'timestamp' => now()->toIso8601String(),
            ]);
            LoggingService::logActivity('Payment', 'create_payout_request', [
                'merchant_order_id' => $request->merchant_order_id,
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

            // Store transaction in database
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'merchant_order_id' => $request->merchant_order_id,
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
                'customer_info' => $request->customer,
                'browser_info' => $request->browser_info ?? null,
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
            // Handle API client errors (4xx)
            $statusCode = $e->getResponse()->getStatusCode();
            $responseBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($responseBody, true) ?? ['error' => $responseBody];
            
            // Log error with API response details (no stack trace)
            LoggingService::logApiError('Payment', '/payout', $e->getMessage(), [
                'merchant_order_id' => $request->merchant_order_id ?? null,
                'http_status' => $statusCode,
                'api_error' => $errorData,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $errorData['error_message'] ?? 'Failed to create payout request',
                'error_code' => $errorData['error_code'] ?? null,
            ], $statusCode);
            
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            // Handle API server errors (5xx)
            $statusCode = $e->getResponse()->getStatusCode();
            $responseBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($responseBody, true) ?? ['error' => $responseBody];
            
            LoggingService::logApiError('Payment', '/payout', $e->getMessage(), [
                'merchant_order_id' => $request->merchant_order_id ?? null,
                'http_status' => $statusCode,
                'api_error' => $errorData,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment API server error',
                'error_code' => $errorData['error_code'] ?? null,
            ], 500);
            
        } catch (\Exception $e) {
            // Handle other exceptions (network errors, etc.)
            LoggingService::logApiError('Payment', '/payout', $e->getMessage(), [
                'merchant_order_id' => $request->merchant_order_id ?? null,
                'exception_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payout request: ' . $e->getMessage(),
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
}

