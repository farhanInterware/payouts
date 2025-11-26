

<?php $__env->startSection('title', 'Transactions'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h2><i class="bi bi-receipt-cutoff me-2"></i>My Transactions</h2>
            <p class="text-muted mb-0">View and manage all your payment transactions</p>
        </div>
        <a href="<?php echo e(route('user.transactions.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>New Transaction
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('user.transactions.index')); ?>">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-search me-2"></i>Search
                    </label>
                    <input type="text" name="search" class="form-control" placeholder="Search by Order ID..." value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-funnel me-2"></i>Status
                    </label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="processing" <?php echo e(request('status') == 'processing' ? 'selected' : ''); ?>>Processing</option>
                        <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                        <option value="declined" <?php echo e(request('status') == 'declined' ? 'selected' : ''); ?>>Declined</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-2"></i>Filter
                    </button>
                </div>
                <div class="col-6 col-md-3 d-flex align-items-end">
                    <?php if(request()->hasAny(['status', 'search'])): ?>
                        <a href="<?php echo e(route('user.transactions.index')); ?>" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-2"></i>Clear Filters
                        </a>
                    <?php endif; ?>
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
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <strong><?php echo e($transaction->merchant_order_id); ?></strong>
                                <?php if($transaction->order_id): ?>
                                    <br><small class="text-muted">ID: <?php echo e($transaction->order_id); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo e(number_format($transaction->amount, 2)); ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark"><?php echo e($transaction->currency); ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo e($transaction->status === 'approved' ? 'success' : ($transaction->status === 'declined' ? 'danger' : 'warning')); ?>">
                                    <i class="bi bi-<?php echo e($transaction->status === 'approved' ? 'check-circle' : ($transaction->status === 'declined' ? 'x-circle' : 'clock')); ?> me-1"></i>
                                    <?php echo e(ucfirst($transaction->status)); ?>

                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo e(strtoupper($transaction->pay_method ?? 'N/A')); ?></span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i><?php echo e($transaction->created_at->format('M d, Y')); ?>

                                    <br>
                                    <i class="bi bi-clock me-1"></i><?php echo e($transaction->created_at->format('H:i')); ?>

                                </small>
                            </td>
                            <td>
                                <a href="<?php echo e(route('user.transactions.show', $transaction->id)); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p class="mb-0 mt-3">No transactions found</p>
                                    <?php if(request()->hasAny(['status', 'search'])): ?>
                                        <a href="<?php echo e(route('user.transactions.index')); ?>" class="btn btn-outline-primary mt-3">
                                            <i class="bi bi-arrow-left me-2"></i>Clear Filters
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo e(route('user.transactions.create')); ?>" class="btn btn-primary mt-3">
                                            <i class="bi bi-plus-circle me-2"></i>Create Transaction
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if($transactions->hasPages()): ?>
        <div class="card-footer">
            <div class="d-flex justify-content-center">
                <?php echo e($transactions->appends(request()->query())->links()); ?>

            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Debitly\resources\views/user/transactions/index.blade.php ENDPATH**/ ?>