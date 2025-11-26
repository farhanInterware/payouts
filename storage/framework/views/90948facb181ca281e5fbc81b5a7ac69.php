

<?php $__env->startSection('title', 'Admin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<h2>Admin Dashboard</h2>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <h3><?php echo e($stats['total_users']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Total Transactions</h5>
                <h3><?php echo e($stats['total_transactions']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Approved</h5>
                <h3><?php echo e($stats['approved_transactions']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title">Declined</h5>
                <h3><?php echo e($stats['declined_transactions']); ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Recent Transactions</h5>
    </div>
    <div class="card-body">
        <table class="table">
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
                <?php $__empty_1 = true; $__currentLoopData = $recentTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($transaction->merchant_order_id); ?></td>
                        <td><?php echo e($transaction->user->name); ?></td>
                        <td><?php echo e($transaction->amount); ?> <?php echo e($transaction->currency); ?></td>
                        <td>
                            <span class="badge bg-<?php echo e($transaction->status === 'approved' ? 'success' : ($transaction->status === 'declined' ? 'danger' : 'warning')); ?>">
                                <?php echo e(ucfirst($transaction->status)); ?>

                            </span>
                        </td>
                        <td><?php echo e($transaction->created_at->format('Y-m-d H:i')); ?></td>
                        <td>
                            <a href="<?php echo e(route('admin.transactions.show', $transaction->id)); ?>" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="text-center">No transactions yet</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Debitly\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>