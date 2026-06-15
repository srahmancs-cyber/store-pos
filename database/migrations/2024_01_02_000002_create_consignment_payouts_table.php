<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tracks what the store owes each vendor for sold consignment items
        Schema::create('consignment_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consignment_vendor_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedBigInteger('total_sales_amount');     // sum of sale prices (cents)
            $table->unsignedBigInteger('store_commission_amount'); // what the store keeps (cents)
            $table->unsignedBigInteger('vendor_payout_amount');    // what the vendor receives (cents)
            $table->unsignedInteger('items_sold');
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->date('paid_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['consignment_vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consignment_payouts');
    }
};
