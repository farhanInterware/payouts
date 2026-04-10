<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    /**
     * Apply index-page filters (status, optional user_id, search on orders + optional related user).
     */
    public function scopeFilterList(Builder $query, Request $request, bool $searchRelatedUser = false): Builder
    {
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->whereRaw('LOWER(status) = ?', [strtolower($request->status)]);
        }

        if ($request->filled('search')) {
            $term = trim($request->search);
            $like = '%'.addcslashes($term, '%_\\').'%';
            $likeLower = '%'.addcslashes(Str::lower($term), '%_\\').'%';
            $driver = $query->getConnection()->getDriverName();

            $query->where(function (Builder $q) use ($like, $likeLower, $driver, $searchRelatedUser) {
                $q->where('merchant_order_id', 'like', $like);

                if ($driver === 'pgsql') {
                    $q->orWhereRaw('CAST(order_id AS TEXT) ILIKE ?', [$like])
                        ->orWhereRaw('CAST(operation_id AS TEXT) ILIKE ?', [$like]);
                } elseif ($driver === 'sqlite') {
                    $q->orWhereRaw('CAST(order_id AS TEXT) LIKE ?', [$like])
                        ->orWhereRaw('CAST(operation_id AS TEXT) LIKE ?', [$like]);
                } else {
                    $q->orWhere('order_id', 'like', $like)
                        ->orWhere('operation_id', 'like', $like);
                }

                // Payout customer email stored on the row (always present for API-created txs)
                $q->orWhere('customer_info->email', 'like', $like);

                if ($searchRelatedUser) {
                    $q->orWhereHas('user', function (Builder $uq) use ($likeLower) {
                        $uq->whereRaw('LOWER(name) LIKE ?', [$likeLower])
                            ->orWhereRaw('LOWER(email) LIKE ?', [$likeLower]);
                    });
                }
            });
        }

        return $query;
    }
}

