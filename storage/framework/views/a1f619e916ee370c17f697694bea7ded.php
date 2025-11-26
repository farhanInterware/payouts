

<?php $__env->startSection('title', 'Create Transaction'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h2><i class="bi bi-plus-circle me-2"></i>Create New Transaction</h2>
            <p class="text-muted mb-0">Fill in the details to create a new payment transaction</p>
        </div>
        <a href="<?php echo e(route('user.transactions.index')); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form id="transactionForm">
            <?php echo csrf_field(); ?>
            
            <!-- Basic Information Section -->
            <div class="mb-4">
                <h5 class="mb-3 pb-2 border-bottom">
                    <i class="bi bi-info-circle me-2 text-primary"></i>Basic Information
                </h5>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-hash me-1"></i>Merchant Order ID <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="merchant_order_id" class="form-control" value="order-<?php echo e(time()); ?>" required>
                        <small class="text-muted">Unique identifier for this order</small>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-file-text me-1"></i>Order Description <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="order_desc" class="form-control" placeholder="Enter order description" required>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-currency-dollar me-1"></i>Amount <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-globe me-1"></i>Currency <span class="text-danger">*</span>
                        </label>
                        <select name="currency" class="form-select" required>
                            <option value="EUR">EUR - Euro</option>
                            <option value="USD">USD - US Dollar</option>
                            <option value="GBP">GBP - British Pound</option>
                        </select>
                    </div>
                        <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-credit-card me-1"></i>Payment Method <span class="text-danger">*</span>
                        </label>
                        <select name="pay_method" id="pay_method" class="form-select" required>
                            <option value="sepa">SEPA</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Merchant Custom Data Section -->
            <div class="mb-4">
                <h5 class="mb-3 pb-2 border-bottom">
                    <i class="bi bi-tags me-2 text-info"></i>Merchant Custom Data <span class="text-muted small">(Optional)</span>
                </h5>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Custom Property 1</label>
                        <input type="text" name="merchant_custom_data[property1]" class="form-control" placeholder="e.g., string">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Custom Property 2</label>
                        <input type="text" name="merchant_custom_data[property2]" class="form-control" placeholder="e.g., string">
                    </div>
                </div>
                <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Leave empty to send empty object {}</small>
            </div>

            <!-- Customer Information Section -->
            <div class="mb-4">
                <h5 class="mb-3 pb-2 border-bottom">
                    <i class="bi bi-person me-2 text-success"></i>Customer Information
                </h5>
                <div class="row g-3">
                        <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-person-badge me-1"></i>Customer ID <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="customer[id]" class="form-control" placeholder="Enter customer ID" required>
                    </div>
                        <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-envelope me-1"></i>Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" name="customer[email]" class="form-control" placeholder="customer@example.com" required>
                    </div>
                        <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-geo-alt me-1"></i>IP Address <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="customer[ip_address]" class="form-control" value="<?php echo e(request()->ip()); ?>" required>
                    </div>
                </div>
            </div>

            <!-- Requisites Section -->
            <div class="mb-4">
                <h5 class="mb-3 pb-2 border-bottom">
                    <i class="bi bi-bank me-2 text-warning"></i>Payment Requisites (SEPA)
                </h5>
                <div id="sepa-requisites">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-123 me-1"></i>Account Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="requisites[account_number]" class="form-control" placeholder="Enter account number" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person me-1"></i>Account Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="requisites[account_name]" class="form-control" placeholder="Enter account holder name" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person me-1"></i>First Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="requisites[customer][first_name]" class="form-control" placeholder="Enter first name" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person me-1"></i>Last Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="requisites[customer][last_name]" class="form-control" placeholder="Enter last name" required>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-globe me-1"></i>Country <span class="text-danger">*</span>
                            </label>
                            <select name="requisites[customer][address][country]" class="form-select" required>
                                <option value="">Select Country</option>
                                <option value="GB">United Kingdom (GB)</option>
                                <option value="US">United States (US)</option>
                                <option value="IT">Italy (IT)</option>
                                <option value="DE">Germany (DE)</option>
                                <option value="FR">France (FR)</option>
                                <option value="ES">Spain (ES)</option>
                                <option value="NL">Netherlands (NL)</option>
                                <option value="BE">Belgium (BE)</option>
                                <option value="AT">Austria (AT)</option>
                                <option value="PT">Portugal (PT)</option>
                                <option value="IE">Ireland (IE)</option>
                                <option value="LU">Luxembourg (LU)</option>
                                <option value="FI">Finland (FI)</option>
                                <option value="GR">Greece (GR)</option>
                                <option value="PL">Poland (PL)</option>
                                <option value="CZ">Czech Republic (CZ)</option>
                                <option value="SE">Sweden (SE)</option>
                                <option value="DK">Denmark (DK)</option>
                                <option value="NO">Norway (NO)</option>
                                <option value="CH">Switzerland (CH)</option>
                            </select>
                            <small class="text-muted">Must be a valid 2-letter ISO country code</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-geo-alt me-1"></i>Address <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="requisites[customer][address][address1]" class="form-control" placeholder="Enter street address" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="<?php echo e(route('user.transactions.index')); ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="bi bi-check-circle me-2"></i>Create Transaction
                </button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    $('#transactionForm').on('submit', function(e) {
        e.preventDefault();
        
        // Build form data object
        const formData = {
            merchant_order_id: $('input[name="merchant_order_id"]').val(),
            order_desc: $('input[name="order_desc"]').val(),
            amount: $('input[name="amount"]').val(),
            currency: $('select[name="currency"]').val(),
            pay_method: $('select[name="pay_method"]').val(),
            customer: {
                id: $('input[name="customer[id]"]').val(),
                email: $('input[name="customer[email]"]').val(),
                ip_address: $('input[name="customer[ip_address]"]').val()
            },
            requisites: {
                account_number: $('input[name="requisites[account_number]"]').val(),
                account_name: $('input[name="requisites[account_name]"]').val(),
                customer: {
                    first_name: $('input[name="requisites[customer][first_name]"]').val(),
                    last_name: $('input[name="requisites[customer][last_name]"]').val(),
                    address: {
                        country: $('select[name="requisites[customer][address][country]"]').val().toUpperCase(),
                        address1: $('input[name="requisites[customer][address][address1]"]').val()
                    }
                }
            }
        };

        // Add merchant_custom_data if provided
        const customData1 = $('input[name="merchant_custom_data[property1]"]').val();
        const customData2 = $('input[name="merchant_custom_data[property2]"]').val();
        if (customData1 || customData2) {
            formData.merchant_custom_data = {};
            if (customData1) formData.merchant_custom_data.property1 = customData1;
            if (customData2) formData.merchant_custom_data.property2 = customData2;
        }

        // Add browser info
        formData.customer.browser_info = {
            user_agent: navigator.userAgent,
            accept_header: 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            javascript_enabled: true,
            language: navigator.language,
            color_depth: screen.colorDepth.toString(),
            timezone: new Date().getTimezoneOffset().toString(),
            java_enabled: false,
            screen_height: screen.height,
            screen_width: screen.width
        };

        const btn = $('#submitBtn');
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        $.ajax({
            url: '<?php echo e(route("user.transactions.store")); ?>',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = '<?php echo e(route("user.transactions.index")); ?>';
                } else {
                    alert('Error: ' + (response.message || 'Unknown error'));
                    btn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'An error occurred';
                alert('Error: ' + error);
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Debitly\resources\views/user/transactions/create.blade.php ENDPATH**/ ?>