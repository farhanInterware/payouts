<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\LoggingService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        LoggingService::logActivity('Admin', 'view_transactions', [
            'filters' => $request->only(['status', 'user_id', 'search']),
        ], auth()->id());

        $query = Transaction::with('user');

        // Filter by status (only if status is provided and not empty)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user (only if user_id is provided and not empty)
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('merchant_order_id', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%")
                  ->orWhere('operation_id', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest()->paginate(15);

        return view('admin.transactions.index', compact('transactions'));
    }

    public function show($id)
    {
        $transaction = Transaction::with('user')->findOrFail($id);
        
        LoggingService::logActivity('Admin', 'view_transaction', [
            'transaction_id' => $transaction->id,
            'merchant_order_id' => $transaction->merchant_order_id,
            'user_id' => $transaction->user_id,
        ], auth()->id());
        
        return view('admin.transactions.show', compact('transaction'));
    }
}

