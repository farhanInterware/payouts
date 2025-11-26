@extends('layouts.app')

@section('title', 'Transaction Details')

@section('content')
<div class="page-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h2><i class="bi bi-receipt me-2"></i>Transaction Details</h2>
            <p class="text-muted mb-0">View complete transaction information</p>
        </div>
        <a href="{{ route('user.transactions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-8">
        <div class="card mb-3">
            <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Transaction Information</h5>
                <span class="badge bg-{{ $transaction->status === 'approved' ? 'success' : ($transaction->status === 'declined' ? 'danger' : 'warning') }} fs-6 px-3 py-2">
                    <i class="bi bi-{{ $transaction->status === 'approved' ? 'check-circle' : ($transaction->status === 'declined' ? 'x-circle' : 'clock') }} me-1"></i>
                    {{ ucfirst($transaction->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-semibold">Merchant Order ID</label>
                            <div class="fw-semibold fs-5">{{ $transaction->merchant_order_id }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-semibold">Order ID</label>
                            <div class="fw-semibold">{{ $transaction->order_id ?? '<span class="text-muted">N/A</span>' }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-semibold">Operation ID</label>
                            <div class="fw-semibold">{{ $transaction->operation_id ?? '<span class="text-muted">N/A</span>' }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-semibold">Amount</label>
                            <div class="fw-bold fs-4 text-primary">
                                {{ number_format($transaction->amount, 2) }} 
                                <span class="badge bg-light text-dark ms-2">{{ $transaction->currency }}</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-semibold">Payment Method</label>
                            <div>
                                <span class="badge bg-info">{{ strtoupper($transaction->pay_method ?? 'N/A') }}</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-semibold">Order Description</label>
                            <div class="fw-semibold">{{ $transaction->order_desc ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-semibold">
                                <i class="bi bi-calendar3 me-1"></i>Created At
                            </label>
                            <div>{{ $transaction->created_at->format('F d, Y') }}</div>
                            <small class="text-muted">{{ $transaction->created_at->format('h:i A') }}</small>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-semibold">
                                <i class="bi bi-clock-history me-1"></i>Finished At
                            </label>
                            <div>
                                @if($transaction->finished_at)
                                    {{ $transaction->finished_at->format('F d, Y') }}
                                    <br><small class="text-muted">{{ $transaction->finished_at->format('h:i A') }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($transaction->error_message)
                    <div class="alert alert-danger mt-3">
                        <h6 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Error Details</h6>
                        <p class="mb-1"><strong>Message:</strong> {{ $transaction->error_message }}</p>
                        @if($transaction->error_code)
                            <p class="mb-0"><strong>Error Code:</strong> <code>{{ $transaction->error_code }}</code></p>
                        @endif
                    </div>
                @endif

                @if($transaction->order_id && $transaction->operation_id)
                    <div class="mt-4">
                        <button class="btn btn-primary" onclick="checkStatus()" id="checkStatusBtn">
                            <i class="bi bi-arrow-clockwise me-2"></i>Check Status
                        </button>
                    </div>
                @endif
            </div>
        </div>

        @if($transaction->requisites)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment Requisites</h5>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code>{{ json_encode($transaction->requisites, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
        </div>
        @endif
    </div>

    <div class="col-12 col-md-4 mt-3 mt-md-0">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('user.transactions.index') }}" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-list-ul me-2"></i>All Transactions
                </a>
                <a href="{{ route('user.transactions.create') }}" class="btn btn-primary w-100">
                    <i class="bi bi-plus-circle me-2"></i>New Transaction
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function checkStatus() {
    const btn = document.getElementById('checkStatusBtn');
    const originalHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Checking...';
    
    $.ajax({
        url: '{{ route("user.transactions.check-status", $transaction->id) }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + (response.message || 'Unknown error'));
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.message || 'Error checking status';
            alert('Error: ' + error);
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });
}
</script>
@endpush
@endsection
