<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['utilities', 'rent', 'internet', 'marketing', 'repairs', 'supplies', 'other']);
            $table->unsignedBigInteger('amount');
            $table->string('description', 500)->nullable();
            $table->date('date');
            $table->enum('payment_method', ['cash', 'bank', 'card'])->default('cash');
            $table->string('receipt_image')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->unsignedTinyInteger('recurring_day_of_month')->nullable(); // valid range 1-28
            $table->unsignedBigInteger('parent_expense_id')->nullable(); // self-referential for recurring instances
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('date');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
