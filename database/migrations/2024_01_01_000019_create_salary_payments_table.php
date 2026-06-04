<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->unsignedTinyInteger('period_month'); // 1-12
            $table->unsignedSmallInteger('period_year');
            $table->enum('payment_method', ['cash', 'bank'])->default('cash');
            $table->date('paid_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('period_month');
            $table->index('period_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
