<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            // 'owner' = store owner, 'investor' = external investor
            $table->enum('type', ['owner', 'investor'])->default('owner')->after('name');
            // Investors may have a specific contribution amount and agreement end date
            $table->unsignedBigInteger('contribution_amount')->default(0)->after('profit_share_percentage');
            // cents — investor's total capital contributed (for reference / ROI display)
            $table->date('agreement_start_date')->nullable()->after('contribution_amount');
            $table->date('agreement_end_date')->nullable()->after('agreement_start_date');
            // Profit share basis for investors:
            // 'net_profit' = share of total net profit (same as owners)
            // 'sales_revenue' = share of gross sales (less common but supported)
            $table->enum('profit_basis', ['net_profit', 'sales_revenue'])->default('net_profit')->after('agreement_end_date');
        });
    }

    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->dropColumn(['type', 'contribution_amount', 'agreement_start_date', 'agreement_end_date', 'profit_basis']);
        });
    }
};
