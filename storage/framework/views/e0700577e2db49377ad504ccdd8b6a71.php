

<?php $__env->startSection('title', 'Transactions'); ?>

<?php $__env->startSection('content'); ?>
<h2>All Transactions</h2>

<div class="card">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('admin.transactions.index')); ?>" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="processing" <?php echo e(request('status') === 'processing' ? 'selected' : ''); ?>>Processing</option>
                        <option value="approved" <?php echo e(request('status') === 'approved' ? 'selected' : ''); ?>>Approved</option>
                        <option value="declined" <?php echo e(request('status') === 'declined' ? 'selected' : ''); ?>>Declined</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment Method</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($transaction->merchant_order_id); ?></td>
                        <td><?php echo e($transaction->user->name); ?></td>
                        <td><?php echo e($transaction->amount); ?> <?php echo e($transaction->currency); ?></td>
                        <td>
                            <span class="badge bg-<?php echo e($transaction->status === 'approved' ? 'success' : ($transaction->status === 'declined' ? 'danger' : 'warning')); ?>">
                                <?php echo e(ucfirst($transaction->status)); ?>

                            </span>
                        </td>
                        <td><?php echo e($transaction->pay_method ?? 'N/A'); ?></td>
                        <td><?php echo e($transaction->created_at->format('Y-m-d H:i')); ?></td>
                        <td>
                            <a href="<?php echo e(route('admin.transactions.show', $transaction->id)); ?>" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="text-center">No transactions found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php echo e($transactions->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Debitly\resources\views/admin/transactions/index.blade.php ENDPATH**/ ?>