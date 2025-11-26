<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Payouts Payment API integration
    |
    */

    'api_url' => env('PAYMENT_API_URL', 'https://sandbox-api.debitly.tech'),
    
    'merchant_id' => (int) env('PAYMENT_MERCHANT_ID', 85),
    
    'merchant_control_value' => env('PAYMENT_MERCHANT_CONTROL_VALUE', '1ecacb670059bcb7943a9f67da5ccfee441edbc3'),

    /*
    |--------------------------------------------------------------------------
    | Payment Endpoints
    |--------------------------------------------------------------------------
    */
    
    'endpoints' => [
        'payout' => '/payout',
        'status' => '/status',
    ],

    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    */
    
    'callback_url' => env('PAYMENT_CALLBACK_URL', env('APP_URL') . '/api/payment/callback'),
    'result_url' => env('PAYMENT_RESULT_URL', env('APP_URL') . '/payment/result'),
    'processing_url' => env('PAYMENT_PROCESSING_URL', env('APP_URL') . '/payment/processing'),
    'success_url' => env('PAYMENT_SUCCESS_URL', env('APP_URL') . '/payment/success'),
    'fail_url' => env('PAYMENT_FAIL_URL', env('APP_URL') . '/payment/fail'),
    'back_url' => env('PAYMENT_BACK_URL', env('APP_URL') . '/'),
];

