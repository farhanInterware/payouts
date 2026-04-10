<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController;
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

        $transactions = Transaction::with('user')
            ->filterList($request, true)
            ->latest()
            ->paginate(15);

        return view('admin.transactions.index', compact('transactions'));
    }

    public function create()
    {
        return view('admin.transactions.create');
    }

    public function store(Request $request)
    {
        $paymentController = new PaymentController();
        return $paymentController->createPayout($request);
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

