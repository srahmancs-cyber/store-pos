<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profit_calculations', function (Blueprint $table) {
            $table->id();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedBigInteger('total_sales_revenue')->default(0);
            $table->unsignedBigInteger('cogs')->default(0);
            $table->unsignedBigInteger('total_expenses')->default(0);
            $table->unsignedBigInteger('total_salaries')->default(0);
            $table->unsignedBigInteger('written_off_loans')->default(0);
            $table->unsignedBigInteger('donations_given')->default(0);
            $table->unsignedBigInteger('other_income')->default(0);
            $table->bigInteger('net_profit')->default(0); // can be negative
            $table->json('details_json')->nullable();
            $table->timestamp('finalised_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('period_start');
            $table->index('period_end');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profit_calculations');
    }
};
