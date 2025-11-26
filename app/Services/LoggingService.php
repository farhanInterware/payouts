<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LoggingService
{
    /**
     * Log API request
     */
    public static function logApiRequest(string $module, string $endpoint, array $requestData, ?string $method = 'POST'): void
    {
        $logData = [
            'module' => $module,
            'endpoint' => $endpoint,
            'method' => $method,
            'request' => $requestData,
            'timestamp' => now()->toIso8601String(),
        ];

        Log::channel('api')->info("API Request - {$module}", $logData);
    }

    /**
     * Log API response
     */
    public static function logApiResponse(string $module, string $endpoint, array $responseData, int $statusCode = 200): void
    {
        $logData = [
            'module' => $module,
            'endpoint' => $endpoint,
            'status_code' => $statusCode,
            'response' => $responseData,
            'timestamp' => now()->toIso8601String(),
        ];

        Log::channel('api')->info("API Response - {$module}", $logData);
    }

    /**
     * Log API error
     */
    public static function logApiError(string $module, string $endpoint, string $error, array $context = []): void
    {
        $logData = [
            'module' => $module,
            'endpoint' => $endpoint,
            'error' => $error,
            'context' => $context,
            'timestamp' => now()->toIso8601String(),
        ];

        Log::channel('api')->error("API Error - {$module}", $logData);
    }

    /**
     * Log module activity
     */
    public static function logActivity(string $module, string $action, array $data = [], ?int $userId = null): void
    {
        $logData = [
            'module' => $module,
            'action' => $action,
            'user_id' => $userId ?? auth()->id(),
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ];

        Log::channel('activity')->info("Activity - {$module} - {$action}", $logData);
    }

    /**
     * Log transaction activity
     */
    public static function logTransaction(string $action, array $transactionData, ?int $userId = null): void
    {
        $logData = [
            'action' => $action,
            'user_id' => $userId ?? auth()->id(),
            'transaction' => $transactionData,
            'timestamp' => now()->toIso8601String(),
        ];

        Log::channel('transactions')->info("Transaction - {$action}", $logData);
    }

    /**
     * Log payment callback
     */
    public static function logCallback(string $type, array $callbackData, bool $verified = false): void
    {
        $logData = [
            'type' => $type,
            'verified' => $verified,
            'data' => $callbackData,
            'timestamp' => now()->toIso8601String(),
        ];

        Log::channel('callbacks')->info("Callback - {$type}", $logData);
    }
}

