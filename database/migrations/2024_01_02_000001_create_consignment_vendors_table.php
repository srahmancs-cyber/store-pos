<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consignment_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('default_commission_rate', 5, 2)->default(30.00); // store keeps this %
            $table->enum('commission_basis', ['sale_price', 'profit'])->default('sale_price');
            // sale_price: commission = sale_price * rate%
            // profit: commission = (sale_price - cost_price) * rate%
            $table->enum('payout_frequency', ['on_sale', 'weekly', 'monthly'])->default('monthly');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Add consignment fields to products table
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('consignment_vendor_id')->nullable()->after('supplier_id');
            $table->decimal('consignment_rate', 5, 2)->nullable()->after('consignment_vendor_id');
            // null = use vendor default rate
            $table->enum('consignment_basis', ['sale_price', 'profit'])->nullable()->after('consignment_rate');
            $table->boolean('is_consignment')->default(false)->after('consignment_basis');

            $table->foreign('consignment_vendor_id')
                  ->references('id')->on('consignment_vendors')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['consignment_vendor_id']);
            $table->dropColumn(['consignment_vendor_id', 'consignment_rate', 'consignment_basis', 'is_consignment']);
        });
        Schema::dropIfExists('consignment_vendors');
    }
};
