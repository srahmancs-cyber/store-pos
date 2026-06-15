<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Link a product to an investor — means this product was purchased using investor funds
            $table->unsignedBigInteger('investor_id')->nullable()->after('consignment_vendor_id');
            $table->foreign('investor_id')->references('id')->on('owners')->nullOnDelete();
        });

        // contribution_amount on owners is now computed — make it nullable so we never store it
        // (it will be calculated dynamically from linked products)
        // The column stays for any manually added cash investments via owner_transactions
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['investor_id']);
            $table->dropColumn('investor_id');
        });
    }
};
