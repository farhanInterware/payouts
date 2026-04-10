@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="page-header">
    <div>
        <h2><i class="bi bi-receipt-cutoff me-2"></i>My Transactions</h2>
        <p class="text-muted mb-0">Withdrawals initiated by your administrator appear here. You can view status and details only.</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('user.transactions.index') }}">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-search me-2"></i>Search
                    </label>
                    <input type="text" name="search" class="form-control" placeholder="Search by Order ID..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-funnel me-2"></i>Status
                    </label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="declined" {{ request('status') == 'declined' ? 'selected' : '' }}>Declined</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-2"></i>Filter
                    </button>
                </div>
                <div class="col-6 col-md-3 d-flex align-items-end">
                    @if(request()->hasAny(['status', 'search']))
                        <a href="{{ route('user.transactions.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-2"></i>Clear Filters
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Transaction List</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Amount</th>
                        <th>Currency</th>
                        <th>Status</th>
                        <th>Payment Method</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>
                                <strong>{{ $transaction->merchant_order_id }}</strong>
                                @if($transaction->order_id)
                                    <br><small class="text-muted">ID: {{ $transaction->order_id }}</small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ number_format($transaction->amount, 2) }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $transaction->currency }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $transaction->status === 'approved' ? 'success' : ($transaction->status === 'declined' ? 'danger' : 'warning') }}">
                                    <i class="bi bi-{{ $transaction->status === 'approved' ? 'check-circle' : ($transaction->status === 'declined' ? 'x-circle' : 'clock') }} me-1"></i>
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ strtoupper($transaction->pay_method ?? 'N/A') }}</span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $transaction->created_at->format('M d, Y') }}
                                    <br>
                                    <i class="bi bi-clock me-1"></i>{{ $transaction->created_at->format('H:i') }}
                                </small>
                            </td>
                            <td>
                                <a href="{{ route('user.transactions.show', $transaction->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p class="mb-0 mt-3">No transactions found</p>
                                    @if(request()->hasAny(['status', 'search']))
                                        <a href="{{ route('user.transactions.index') }}" class="btn btn-outline-primary mt-3">
                                            <i class="bi bi-arrow-left me-2"></i>Clear Filters
                                        </a>
                                    @else
                                        <p class="text-muted small mt-3 mb-0">No withdrawals yet. Your administrator will process payouts on your behalf.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transactions->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-center">
                {{ $transactions->withQueryString()->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
