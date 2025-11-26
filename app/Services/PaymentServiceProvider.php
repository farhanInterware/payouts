<?php

declare(strict_types=1);

namespace App\Services;

class PaymentServiceProvider
{
    private string $merchantControlValue;

    public function __construct(string $merchantControlValue)
    {
        $this->merchantControlValue = $merchantControlValue;
    }

    public function sign(string $json): string
    {
        $requestBody = json_decode($json, true);
        if (!is_array($requestBody)) {
            throw new \RuntimeException("Failed to decode the JSON string.");
        }

        $sortedAndCleanedBody = $this->recursiveSortAndClean($requestBody);
        $finalBody = array_filter($sortedAndCleanedBody, function ($value): bool {
            return !is_null($value);
        });

        $bodyString = json_encode($finalBody, JSON_UNESCAPED_SLASHES);
        if ($bodyString === false) {
            throw new \RuntimeException("Failed to encode the sorted and cleaned body to JSON.");
        }

        $signedString = $bodyString . $this->merchantControlValue;
        $signature = $this->generateSHA256($signedString);
        
        // Add signature to finalBody (custom_data is already stdClass if empty from recursiveSortAndClean)
        $finalBody['signature'] = $signature;

        $finalJson = json_encode($finalBody, JSON_UNESCAPED_SLASHES);
        if ($finalJson === false) {
            throw new \RuntimeException("Failed to encode the final body with signature to JSON.");
        }

        return $finalJson;
    }

    public function verify(string $json): bool
    {
        $callbackData = json_decode($json, true);
        if (!is_array($callbackData)) {
            throw new \RuntimeException("Failed to decode the JSON string.");
        }

        // Extract the signature from callback (sent by payment provider)
        if (!isset($callbackData['signature'])) {
            throw new \RuntimeException("Signature not found in callback data.");
        }
        $receivedSignature = $callbackData['signature'];

        // Remove signature from data for verification
        unset($callbackData['signature']);

        // Sort and clean the data (same process as signing)
        $sortedAndCleanedBody = $this->recursiveSortAndClean($callbackData);
        $finalBody = array_filter($sortedAndCleanedBody, function ($value): bool {
            return !is_null($value);
        });

        // Generate signature from cleaned data
        $bodyString = json_encode($finalBody, JSON_UNESCAPED_SLASHES);
        if ($bodyString === false) {
            throw new \RuntimeException("Failed to encode the sorted and cleaned body to JSON.");
        }

        $signedString = $bodyString . $this->merchantControlValue;
        $calculatedSignature = $this->generateSHA256($signedString);

        // Compare signatures using timing-safe comparison
        return hash_equals($receivedSignature, $calculatedSignature);
    }

    public function verifyFormData(array $data): bool
    {
        // Extract signature from form data
        if (!isset($data['signature'])) {
            throw new \RuntimeException("Signature not found in form data.");
        }
        $receivedSignature = $data['signature'];

        // Remove signature from data for verification
        unset($data['signature']);

        // Convert form data to array structure similar to JSON
        $callbackData = $this->normalizeFormData($data);

        // Sort and clean the data
        $sortedAndCleanedBody = $this->recursiveSortAndClean($callbackData);
        $finalBody = array_filter($sortedAndCleanedBody, function ($value): bool {
            return !is_null($value);
        });

        // Generate signature from cleaned data
        $bodyString = json_encode($finalBody, JSON_UNESCAPED_SLASHES);
        if ($bodyString === false) {
            throw new \RuntimeException("Failed to encode the sorted and cleaned body to JSON.");
        }

        $signedString = $bodyString . $this->merchantControlValue;
        $calculatedSignature = $this->generateSHA256($signedString);

        // Compare signatures using timing-safe comparison
        return hash_equals($receivedSignature, $calculatedSignature);
    }

    private function normalizeFormData(array $data): array
    {
        // Form data comes as flat array, convert to structure similar to JSON callback
        return $data;
    }

    private function recursiveSortAndClean(array $input): array
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
                    $input[$key] = $this->recursiveSortAndClean($value);
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

    private function generateSHA256(string $input): string
    {
        return hash('sha256', $input);
    }

    private function convertEmptyArraysToObjects($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    if (empty($value) && $key === 'custom_data') {
                        // Convert empty array to stdClass so it encodes as {} not []
                        $data[$key] = new \stdClass();
                    } else {
                        $data[$key] = $this->convertEmptyArraysToObjects($value);
                    }
                }
            }
        }
        return $data;
    }
}

