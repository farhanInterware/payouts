<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'merchant_order_id',
        'order_id',
        'operation_id',
        'operation_type',
        'amount',
        'currency',
        'status',
        'pay_method',
        'order_desc',
        'error_message',
        'error_code',
        'initial_amount',
        'total_refunded_amount',
        'customer_id',
        'merchant_id',
        'merchant_custom_data',
        'requisites',
        'bin_data',
        'customer_info',
        'browser_info',
        'created_at',
        'finished_at',
    ];

    protected $casts = [
        'merchant_custom_data' => 'array',
        'requisites' => 'array',
        'bin_data' => 'array',
        'customer_info' => 'array',
        'browser_info' => 'array',
        'amount' => 'decimal:2',
        'initial_amount' => 'decimal:2',
        'total_refunded_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * Get the user that owns the transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if transaction is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if transaction is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if transaction is declined
     */
    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }
}

