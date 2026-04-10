<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController;
use App\Models\Transaction;
use App\Services\LoggingService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        LoggingService::logActivity('User', 'view_transactions', [
            'filters' => $request->only(['status', 'search']),
        ], auth()->id());

        $query = auth()->user()->transactions()->latest();

        // Filter by status (only if status is provided and not empty)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('merchant_order_id', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(15);

        return view('user.transactions.index', compact('transactions'));
    }

    public function show($id)
    {
        $transaction = auth()->user()->transactions()->findOrFail($id);
        
        LoggingService::logActivity('User', 'view_transaction', [
            'transaction_id' => $transaction->id,
            'merchant_order_id' => $transaction->merchant_order_id,
        ], auth()->id());
        
        return view('user.transactions.show', compact('transaction'));
    }

    public function checkStatus(Request $request, $id)
    {
        $transaction = auth()->user()->transactions()->findOrFail($id);
        
        if (!$transaction->order_id || !$transaction->operation_id) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction does not have order_id or operation_id',
            ], 400);
        }

        $paymentController = new PaymentController();
        $request->merge([
            'order_id' => $transaction->order_id,
            'operation_id' => $transaction->operation_id,
        ]);
        
        return $paymentController->checkStatus($request);
    }
}

