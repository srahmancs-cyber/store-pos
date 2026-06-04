<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('description', 500);
            $table->unsignedBigInteger('amount');
            $table->date('due_date');
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->date('paid_date')->nullable();
            $table->enum('payment_method', ['cash', 'bank', 'card'])->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
