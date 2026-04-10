<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController;
use App\Services\LoggingService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        LoggingService::logActivity('User', 'view_transactions', [
            'filters' => $request->only(['status', 'search']),
        ], auth()->id());

        $transactions = auth()->user()->transactions()
            ->filterList($request, true)
            ->latest()
            ->paginate(15);

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

