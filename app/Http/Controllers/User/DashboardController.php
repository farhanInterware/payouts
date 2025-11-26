<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\LoggingService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        LoggingService::logActivity('User', 'view_dashboard', [], auth()->id());
        
        $user = auth()->user();
        
        $stats = [
            'total_transactions' => $user->transactions()->count(),
            'approved_transactions' => $user->transactions()->where('status', 'approved')->count(),
            'declined_transactions' => $user->transactions()->where('status', 'declined')->count(),
            'processing_transactions' => $user->transactions()->where('status', 'processing')->count(),
            'total_amount' => $user->transactions()->where('status', 'approved')->sum('amount'),
        ];

        $recentTransactions = $user->transactions()
            ->latest()
            ->take(10)
            ->get();

        return view('user.dashboard', compact('stats', 'recentTransactions'));
    }
}

