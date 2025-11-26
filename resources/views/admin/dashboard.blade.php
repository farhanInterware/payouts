@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="page-header">
    <h2><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</h2>
    <p class="text-muted mb-0">System overview and statistics</p>
</div>

<div class="row mb-4">
    <div class="col-6 col-sm-6 col-md-3 mb-3">
        <div class="card stat-card bg-primary position-relative" style="background: var(--primary-gradient) !important;">
            <div class="card-body">
                <i class="bi bi-people"></i>
                <h5 class="card-title">Total Users</h5>
                <h3>{{ $stats['total_users'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-md-3 mb-3">
        <div class="card stat-card bg-info position-relative" style="background: var(--info-gradient) !important;">
            <div class="card-body">
                <i class="bi bi-receipt-cutoff"></i>
                <h5 class="card-title">Total Transactions</h5>
                <h3>{{ $stats['total_transactions'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-md-3 mb-3">
        <div class="card stat-card bg-success position-relative" style="background: var(--success-gradient) !important;">
            <div class="card-body">
                <i class="bi bi-check-circle"></i>
                <h5 class="card-title">Approved</h5>
                <h3>{{ $stats['approved_transactions'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-md-3 mb-3">
        <div class="card stat-card bg-danger position-relative" style="background: var(--danger-gradient) !important;">
            <div class="card-body">
                <i class="bi bi-x-circle"></i>
                <h5 class="card-title">Declined</h5>
                <h3>{{ $stats['declined_transactions'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Transactions</h5>
        <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-outline-primary">
            View All <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $transaction)
                        <tr>
                            <td>
                                <strong>{{ $transaction->merchant_order_id }}</strong>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                        <span class="text-white fw-bold">{{ substr($transaction->user->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $transaction->user->name }}</div>
                                        <small class="text-muted">{{ $transaction->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ number_format($transaction->amount, 2) }}</strong>
                                <span class="badge bg-light text-dark ms-1">{{ $transaction->currency }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $transaction->status === 'approved' ? 'success' : ($transaction->status === 'declined' ? 'danger' : 'warning') }}">
                                    <i class="bi bi-{{ $transaction->status === 'approved' ? 'check-circle' : ($transaction->status === 'declined' ? 'x-circle' : 'clock') }} me-1"></i>
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $transaction->created_at->format('M d, Y') }}
                                    <br>
                                    <i class="bi bi-clock me-1"></i>{{ $transaction->created_at->format('H:i') }}
                                </small>
                            </td>
                            <td>
                                <a href="{{ route('admin.transactions.show', $transaction->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p class="mb-0 mt-3">No transactions yet</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
