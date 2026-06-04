<?php

namespace App\Http\Controllers\Finances;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\EmployeeLoan;
use App\Models\Expense;
use App\Models\Owner;
use App\Models\OwnerTransaction;
use App\Models\ProfitCalculation;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalaryPayment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfitController extends Controller
{
    public function index(Request $request)
    {
        $calculations = ProfitCalculation::with('creator')->latest('period_start')->paginate(15);

        return view('finances.profit.index', compact('calculations'));
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
            'other_income' => 'nullable|numeric|min:0',
        ]);

        $periodStart       = Carbon::parse($request->period_start)->startOfDay();
        $periodEnd         = Carbon::parse($request->period_end)->endOfDay();
        $otherIncomeCents  = Money::toCents($request->other_income ?? 0);

        // Check if a calculation already exists for this period
        $existing = ProfitCalculation::where('period_start', $periodStart->toDateString())
            ->where('period_end', $periodEnd->toDateString())
            ->first();

        if ($existing && !$request->boolean('confirm')) {
            return view('finances.profit.confirm', [
                'existing'     => $existing,
                'period_start' => $request->period_start,
                'period_end'   => $request->period_end,
                'other_income' => $request->other_income,
            ]);
        }

        DB::transaction(function () use ($request, $periodStart, $periodEnd, $otherIncomeCents, $existing) {
            // ── Revenue ──────────────────────────────────────────────────────
            $totalSalesRevenue = Sale::where('status', 'completed')
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->sum('final_amount');

            // ── COGS ─────────────────────────────────────────────────────────
            $cogs = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sales.status', 'completed')
                ->whereBetween('sales.created_at', [$periodStart, $periodEnd])
                ->selectRaw('SUM(sale_items.cost_price * sale_items.quantity) as total_cogs')
                ->value('total_cogs') ?? 0;

            // ── Expenses ──────────────────────────────────────────────────────
            $totalExpenses = Expense::whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                ->sum('amount');

            // ── Salaries ──────────────────────────────────────────────────────
            $totalSalaries = SalaryPayment::whereBetween('paid_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                ->sum('amount');

            // ── Written-off loans ────────────────────────────────────────────
            $writtenOffLoans = EmployeeLoan::where('status', 'written_off')
                ->whereBetween('updated_at', [$periodStart, $periodEnd])
                ->sum('remaining_balance');
            // remaining_balance at time of write-off represents the un-recovered amount

            // ── Donations given ───────────────────────────────────────────────
            $donationsGiven = Donation::where('status', 'given')
                ->whereBetween('given_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                ->sum('amount');

            // ── Net Profit ───────────────────────────────────────────────────
            $netProfit = $totalSalesRevenue
                - $cogs
                - $totalExpenses
                - $totalSalaries
                - $writtenOffLoans
                - $donationsGiven
                + $otherIncomeCents;

            $details = [
                'total_sales_revenue' => $totalSalesRevenue,
                'cogs'                => $cogs,
                'total_expenses'      => $totalExpenses,
                'total_salaries'      => $totalSalaries,
                'written_off_loans'   => $writtenOffLoans,
                'donations_given'     => $donationsGiven,
                'other_income'        => $otherIncomeCents,
                'net_profit'          => $netProfit,
                'calculated_at'       => now()->toIso8601String(),
            ];

            // Save or overwrite calculation
            if ($existing) {
                $existing->update([
                    'total_sales_revenue' => $totalSalesRevenue,
                    'cogs'                => $cogs,
                    'total_expenses'      => $totalExpenses,
                    'total_salaries'      => $totalSalaries,
                    'written_off_loans'   => $writtenOffLoans,
                    'donations_given'     => $donationsGiven,
                    'other_income'        => $otherIncomeCents,
                    'net_profit'          => $netProfit,
                    'details_json'        => $details,
                    'finalised_at'        => now(),
                    'created_by'          => Auth::id(),
                ]);
                $calc = $existing;
            } else {
                $calc = ProfitCalculation::create([
                    'period_start'        => $periodStart->toDateString(),
                    'period_end'          => $periodEnd->toDateString(),
                    'total_sales_revenue' => $totalSalesRevenue,
                    'cogs'                => $cogs,
                    'total_expenses'      => $totalExpenses,
                    'total_salaries'      => $totalSalaries,
                    'written_off_loans'   => $writtenOffLoans,
                    'donations_given'     => $donationsGiven,
                    'other_income'        => $otherIncomeCents,
                    'net_profit'          => $netProfit,
                    'details_json'        => $details,
                    'finalised_at'        => now(),
                    'created_by'          => Auth::id(),
                ]);
            }

            // Allocate profit to owners
            if ($netProfit > 0) {
                $owners = Owner::all();
                foreach ($owners as $owner) {
                    $ownerShare = (int) round($netProfit * ($owner->profit_share_percentage / 100));

                    OwnerTransaction::create([
                        'owner_id'         => $owner->id,
                        'type'             => 'profit_allocation',
                        'amount'           => $ownerShare,
                        'transaction_date' => $periodEnd->toDateString(),
                        'notes'            => "Profit allocation for period {$periodStart->toDateString()} – {$periodEnd->toDateString()}",
                        'created_by'       => Auth::id(),
                    ]);
                }
            }

            ActivityLogger::log(
                'profit_calculation',
                "Profit calculated for {$periodStart->toDateString()} – {$periodEnd->toDateString()}: " . Money::format($netProfit),
                ProfitCalculation::class,
                $calc->id
            );
        });

        return redirect()->route('finances.profit')->with('success', 'Profit calculated and owner allocations recorded.');
    }
}
