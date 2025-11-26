@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="page-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h2><i class="bi bi-person me-2"></i>User Details</h2>
            <p class="text-muted mb-0">View user information and transaction history</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Users
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-4 mb-3 mb-md-0">
        <div class="card mb-3">
            <div class="card-body text-center">
                <div class="bg-primary bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <span class="text-white fw-bold" style="font-size: 2rem;">{{ substr($user->name, 0, 1) }}</span>
                </div>
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                @if($user->isAdmin())
                    <span class="badge bg-danger fs-6 px-3 py-2">
                        <i class="bi bi-shield-check me-1"></i>Administrator
                    </span>
                @else
                    <span class="badge bg-secondary fs-6 px-3 py-2">
                        <i class="bi bi-person me-1"></i>User
                    </span>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>User Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-semibold">Name</label>
                    <div class="fw-semibold">{{ $user->name }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-semibold">Email</label>
                    <div class="fw-semibold">{{ $user->email }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-semibold">
                        <i class="bi bi-calendar3 me-1"></i>Registered
                    </label>
                    <div>{{ $user->created_at->format('F d, Y') }}</div>
                    <small class="text-muted">{{ $user->created_at->format('h:i A') }}</small>
                </div>
                <div>
                    <label class="text-muted small text-uppercase fw-semibold">Total Transactions</label>
                    <div>
                        <span class="badge bg-info fs-6">{{ $user->transactions->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-key me-2"></i>Change Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.password', $user->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            <i class="bi bi-key me-1"></i>New Password <span class="text-danger">*</span>
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required>
                            <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Password must be at least 8 characters long.</small>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-semibold">
                            <i class="bi bi-key-fill me-1"></i>Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required>
                            <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-2"></i>Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-receipt-cutoff me-2"></i>User Transactions</h5>
                <span class="badge bg-primary">{{ $user->transactions->count() }} Total</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->transactions as $transaction)
                                <tr>
                                    <td>
                                        <strong>{{ $transaction->merchant_order_id }}</strong>
                                        @if($transaction->order_id)
                                            <br><small class="text-muted">ID: {{ $transaction->order_id }}</small>
                                        @endif
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
                                    <td colspan="5" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="bi bi-inbox"></i>
                                            <p class="mb-0 mt-3">No transactions found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
