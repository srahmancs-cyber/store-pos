<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['sale', 'purchase', 'adjustment', 'return']);
            $table->enum('adjustment_type', ['add', 'remove'])->nullable();
            $table->integer('quantity');
            $table->integer('old_quantity');
            $table->integer('new_quantity');
            $table->string('reason')->nullable();
            $table->string('reference_type')->nullable(); // e.g. 'sale', 'purchase_order'
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps(); // append-only, but using standard timestamps for created_at

            $table->index('product_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
