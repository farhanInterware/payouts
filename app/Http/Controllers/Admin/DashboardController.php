<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Services\LoggingService;

class DashboardController extends Controller
{
    public function index()
    {
        LoggingService::logActivity('Admin', 'view_dashboard', [], auth()->id());
        
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'total_transactions' => Transaction::count(),
            'approved_transactions' => Transaction::where('status', 'approved')->count(),
            'declined_transactions' => Transaction::where('status', 'declined')->count(),
            'processing_transactions' => Transaction::where('status', 'processing')->count(),
            'total_revenue' => Transaction::where('status', 'approved')->sum('amount'),
        ];

        $recentTransactions = Transaction::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentTransactions'));
    }
}

