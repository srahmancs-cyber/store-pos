<?php

use App\Models\Donation;
use App\Models\Expense;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

// ─── Recurring Expenses ───────────────────────────────────────────────────────
// Auto-generate recurring expense instances each month
Schedule::call(function () {
    $today           = Carbon::today();
    $recurringExpenses = \App\Models\Expense::where('is_recurring', true)
        ->whereNotNull('recurring_day_of_month')
        ->whereNull('parent_expense_id') // only templates, not generated children
        ->get();

    foreach ($recurringExpenses as $template) {
        $targetDay = (int) $template->recurring_day_of_month;

        // Only create if today matches the recurring day
        if ($today->day !== $targetDay) {
            continue;
        }

        $alreadyExists = \App\Models\Expense::where('parent_expense_id', $template->id)
            ->whereYear('date', $today->year)
            ->whereMonth('date', $today->month)
            ->exists();

        if (!$alreadyExists) {
            \App\Models\Expense::create([
                'category'               => $template->category,
                'amount'                 => $template->amount,
                'description'            => $template->description,
                'date'                   => $today->toDateString(),
                'payment_method'         => $template->payment_method,
                'is_recurring'           => false,
                'parent_expense_id'      => $template->id,
                'created_by'             => $template->created_by,
            ]);
        }
    }
})->dailyAt('00:05')->name('generate-recurring-expenses')->withoutOverlapping();

// ─── Donation Auto-Calculation ────────────────────────────────────────────────
// Runs at the start of each month/quarter to calculate and create a pending donation
Schedule::call(function () {
    $donationEnabled = Setting::get('donation_enabled', false);

    if (!$donationEnabled) {
        return;
    }

    $frequency  = Setting::get('donation_frequency', 'monthly');
    $today      = Carbon::today();

    // Only run on the first day of a month
    if ($today->day !== 1) {
        return;
    }

    // For quarterly: only run on Jan 1, Apr 1, Jul 1, Oct 1
    if ($frequency === 'quarterly' && !in_array($today->month, [1, 4, 7, 10])) {
        return;
    }

    // Calculate period that just ended
    $periodEnd   = $today->copy()->subDay()->endOfDay();
    $periodStart = $frequency === 'monthly'
        ? $periodEnd->copy()->startOfMonth()
        : $periodEnd->copy()->startOfQuarter();

    // Calculate net profit for the period
    $totalSales  = \App\Models\Sale::where('status', 'completed')
        ->whereBetween('created_at', [$periodStart, $periodEnd])
        ->sum('final_amount');

    $cogs        = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->where('sales.status', 'completed')
        ->whereBetween('sales.created_at', [$periodStart, $periodEnd])
        ->selectRaw('SUM(sale_items.cost_price * sale_items.quantity) as total')
        ->value('total') ?? 0;

    $expenses    = \App\Models\Expense::whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])->sum('amount');
    $salaries    = \App\Models\SalaryPayment::whereBetween('paid_date', [$periodStart->toDateString(), $periodEnd->toDateString()])->sum('amount');
    $donationsGiven = Donation::where('status', 'given')
        ->whereBetween('given_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
        ->sum('amount');

    $netProfit = $totalSales - $cogs - $expenses - $salaries - $donationsGiven;

    if ($netProfit <= 0) {
        return; // No profit, no donation
    }

    $percentage    = (float) Setting::get('donation_percentage', 5);
    $donationAmount = (int) round($netProfit * $percentage / 100);

    if ($donationAmount <= 0) {
        return;
    }

    // Avoid duplicates for the same period
    $exists = Donation::where('period_start', $periodStart->toDateString())
        ->where('period_end', $periodEnd->toDateString())
        ->exists();

    if (!$exists) {
        Donation::create([
            'amount'                 => $donationAmount,
            'calculated_from_profit' => $netProfit,
            'period_start'           => $periodStart->toDateString(),
            'period_end'             => $periodEnd->toDateString(),
            'status'                 => 'pending',
        ]);
    }
})->dailyAt('00:10')->name('auto-calculate-donation')->withoutOverlapping();
