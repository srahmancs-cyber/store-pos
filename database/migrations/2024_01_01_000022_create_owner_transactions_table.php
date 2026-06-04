<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('owners')->cascadeOnDelete();
            $table->enum('type', ['investment', 'withdrawal', 'profit_allocation']);
            $table->unsignedBigInteger('amount');
            $table->date('transaction_date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('capital_injection_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('owner_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_transactions');
    }
};
