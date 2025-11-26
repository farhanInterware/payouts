<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('merchant_order_id')->index();
            $table->string('order_id')->nullable()->index();
            $table->string('operation_id')->nullable()->index();
            $table->string('operation_type')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3);
            $table->string('status')->default('processing')->index();
            $table->string('pay_method')->nullable();
            $table->text('order_desc')->nullable();
            $table->text('error_message')->nullable();
            $table->string('error_code')->nullable();
            $table->decimal('initial_amount', 15, 2)->nullable();
            $table->decimal('total_refunded_amount', 15, 2)->default(0);
            $table->string('customer_id')->nullable();
            $table->integer('merchant_id')->nullable();
            $table->json('merchant_custom_data')->nullable();
            $table->json('requisites')->nullable();
            $table->json('bin_data')->nullable();
            $table->json('customer_info')->nullable();
            $table->json('browser_info')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

