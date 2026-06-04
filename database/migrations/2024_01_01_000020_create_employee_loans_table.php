<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('remaining_balance');
            $table->text('reason')->nullable();
            $table->enum('source_type', ['cash', 'bank']);
            $table->enum('status', ['outstanding', 'repaid', 'written_off'])->default('outstanding');
            $table->boolean('auto_deduct')->default(false);
            $table->unsignedBigInteger('auto_deduct_amount')->default(0);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_loans');
    }
};
