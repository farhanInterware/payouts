

<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
            <p class="text-muted mb-0">Welcome back, <?php echo e(auth()->user()->name); ?>!</p>
        </div>
        <a href="<?php echo e(route('user.transactions.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>New Transaction
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-6 col-sm-6 col-md-3 mb-3">
        <div class="card stat-card bg-primary position-relative" style="background: var(--primary-gradient) !important;">
            <div class="card-body">
                <i class="bi bi-receipt-cutoff"></i>
                <h5 class="card-title">Total Transactions</h5>
                <h3><?php echo e($stats['total_transactions']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-md-3 mb-3">
        <div class="card stat-card bg-success position-relative" style="background: var(--success-gradient) !important;">
            <div class="card-body">
                <i class="bi bi-check-circle"></i>
                <h5 class="card-title">Approved</h5>
                <h3><?php echo e($stats['approved_transactions']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-md-3 mb-3">
        <div class="card stat-card bg-danger position-relative" style="background: var(--danger-gradient) !important;">
            <div class="card-body">
                <i class="bi bi-x-circle"></i>
                <h5 class="card-title">Declined</h5>
                <h3><?php echo e($stats['declined_transactions']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-md-3 mb-3">
        <div class="card stat-card bg-info position-relative" style="background: var(--info-gradient) !important;">
            <div class="card-body">
                <i class="bi bi-currency-dollar"></i>
                <h5 class="card-title">Total Amount</h5>
                <h3><?php echo e(number_format($stats['total_amount'], 2)); ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Transactions</h5>
        <a href="<?php echo e(route('user.transactions.index')); ?>" class="btn btn-sm btn-outline-primary">
            View All <i class="bi bi-arrow-right ms-1"></i>
        </a>
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
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $recentTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <strong><?php echo e($transaction->merchant_order_id); ?></strong>
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
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p class="mb-0 mt-3">No transactions yet</p>
                                    <a href="<?php echo e(route('user.transactions.create')); ?>" class="btn btn-primary mt-3">
                                        <i class="bi bi-plus-circle me-2"></i>Create Your First Transaction
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Debitly\resources\views/user/dashboard.blade.php ENDPATH**/ ?>