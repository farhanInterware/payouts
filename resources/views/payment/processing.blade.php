@extends('layouts.app')

@section('title', 'Payment Processing')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-lg">
            <div class="card-body text-center p-5">
                <div class="mb-4">
                    <div class="bg-warning bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <div class="spinner-border text-white" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <h2 class="fw-bold text-warning mb-2">Payment Processing</h2>
                    <p class="text-muted">
                        @if(session('info'))
                            {{ session('info') }}
                        @else
                            Your transaction is being processed. Please wait...
                        @endif
                    </p>
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <a href="{{ route('user.dashboard') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
                    </a>
                    <a href="{{ route('user.transactions.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-list-ul me-2"></i>View Transactions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
