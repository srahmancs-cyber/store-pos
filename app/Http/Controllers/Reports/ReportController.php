<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Category;
use App\Models\Donation;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\Expense;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\ProfitCalculation;
use App\Models\Sale;
use App\Models\SalaryPayment;
use App\Models\SaleItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // -------------------------------------------------------------------------
    // Shared helpers
    // -------------------------------------------------------------------------

    private function parseDateRange(Request $request): array
    {
        $from = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::now()->endOfMonth();

        return [$from, $to];
    }

    private function currencySymbol(): string
    {
        return Setting::get('currency_symbol', '$');
    }

    // -------------------------------------------------------------------------
    // 1. Sales Report
    // -------------------------------------------------------------------------

    public function sales(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        // Daily totals
        $dailyTotals = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as sale_date, COUNT(*) as count, SUM(final_amount) as revenue')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();

        // Payment method breakdown
        $paymentBreakdown = Payment::join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from, $to])
            ->selectRaw('payment_method, SUM(payments.amount) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        // Top products
        $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from, $to])
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(total) as revenue')
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->take(10)
            ->get();

        $totalRevenue = $dailyTotals->sum('revenue');
        $totalCount   = $dailyTotals->sum('count');

        return view('reports.sales', compact(
            'dailyTotals', 'paymentBreakdown', 'topProducts',
            'totalRevenue', 'totalCount', 'from', 'to'
        ))->with('currencySymbol', $this->currencySymbol());
    }

    // -------------------------------------------------------------------------
    // 2. Category Profit Report
    // -------------------------------------------------------------------------

    public function categoryProfit(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $data = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from, $to])
            ->selectRaw(
                'categories.name as category_name,
                 SUM(sale_items.total) as revenue,
                 SUM(sale_items.cost_price * sale_items.quantity) as cogs,
                 SUM(sale_items.total - (sale_items.cost_price * sale_items.quantity)) as profit,
                 SUM(sale_items.quantity) as total_qty'
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('profit')
            ->get()
            ->map(function ($row) {
                $row->margin = $row->revenue > 0
                    ? round(($row->profit / $row->revenue) * 100, 2)
                    : 0;
                return $row;
            });

        return view('reports.category-profit', compact('data', 'from', 'to'))
            ->with('currencySymbol', $this->currencySymbol());
    }

    // -------------------------------------------------------------------------
    // 3. Expenses Report
    // -------------------------------------------------------------------------

    public function expenses(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $expenses = Expense::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->get();

        $byCategory = $expenses->groupBy('category')->map(fn ($group) => [
            'count'  => $group->count(),
            'amount' => $group->sum('amount'),
        ])->sortByDesc('amount');

        $total = $expenses->sum('amount');

        return view('reports.expenses', compact('expenses', 'byCategory', 'total', 'from', 'to'))
            ->with('currencySymbol', $this->currencySymbol());
    }

    // -------------------------------------------------------------------------
    // 4. Employees Report
    // -------------------------------------------------------------------------

    public function employees(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $employees = Employee::where('is_active', true)->with('user')->get();

        $employeeData = $employees->map(function (Employee $employee) use ($from, $to) {
            // Sales count and revenue (via user_id)
            $salesData = $employee->user_id
                ? Sale::where('user_id', $employee->user_id)
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$from, $to])
                    ->selectRaw('COUNT(*) as count, SUM(final_amount) as revenue')
                    ->first()
                : null;

            // Hours worked
            $totalMinutes = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->sum('duration_minutes');

            return [
                'employee'      => $employee,
                'sales_count'   => $salesData?->count ?? 0,
                'sales_revenue' => $salesData?->revenue ?? 0,
                'hours_worked'  => round($totalMinutes / 60, 2),
            ];
        });

        return view('reports.employees', compact('employeeData', 'from', 'to'))
            ->with('currencySymbol', $this->currencySymbol());
    }

    // -------------------------------------------------------------------------
    // 5. Loans Report
    // -------------------------------------------------------------------------

    public function loans(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $loans = EmployeeLoan::with(['employee', 'repayments' => fn ($q) => $q->orderBy('repayment_date')])
            ->orderBy('status')
            ->paginate(20);

        $totals = [
            'issued'      => EmployeeLoan::whereBetween('created_at', [$from, $to])->sum('amount'),
            'outstanding' => EmployeeLoan::where('status', 'outstanding')->sum('remaining_balance'),
            'repaid'      => EmployeeLoan::where('status', 'repaid')->sum('amount'),
            'written_off' => EmployeeLoan::where('status', 'written_off')->sum('remaining_balance'),
        ];

        return view('reports.loans', compact('loans', 'totals', 'from', 'to'))
            ->with('currencySymbol', $this->currencySymbol());
    }

    // -------------------------------------------------------------------------
    // 6. Owner Equity Report
    // -------------------------------------------------------------------------

    public function ownerEquity(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $owners = Owner::with(['transactions' => fn ($q) => $q->whereBetween('transaction_date', [$from->toDateString(), $to->toDateString()])->orderBy('transaction_date')])
            ->orderBy('sort_order')
            ->get()
            ->map(function (Owner $owner) {
                $allTransactions = $owner->transactions()->orderBy('transaction_date')->get();
                $totalInvested   = $allTransactions->whereIn('type', ['investment'])->sum('amount');
                $totalWithdrawn  = $allTransactions->where('type', 'withdrawal')->sum('amount');
                $totalProfit     = $allTransactions->where('type', 'profit_allocation')->sum('amount');
                $equity          = $totalInvested + $totalProfit - $totalWithdrawn;

                return [
                    'owner'           => $owner,
                    'transactions'    => $owner->transactions,
                    'total_invested'  => $totalInvested,
                    'total_withdrawn' => $totalWithdrawn,
                    'total_profit'    => $totalProfit,
                    'equity'          => $equity,
                ];
            });

        return view('reports.owner-equity', compact('owners', 'from', 'to'))
            ->with('currencySymbol', $this->currencySymbol());
    }

    // -------------------------------------------------------------------------
    // 7. Profit & Loss Report
    // -------------------------------------------------------------------------

    public function profitLoss(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $revenue = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->sum('final_amount');

        $cogs = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from, $to])
            ->selectRaw('SUM(sale_items.cost_price * sale_items.quantity) as total')
            ->value('total') ?? 0;

        $grossProfit = $revenue - $cogs;

        $expenses = Expense::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $totalExpenses = $expenses->sum('total');

        $salaries    = SalaryPayment::whereBetween('paid_date', [$from->toDateString(), $to->toDateString()])->sum('amount');
        $donations   = Donation::where('status', 'given')->whereBetween('given_date', [$from->toDateString(), $to->toDateString()])->sum('amount');
        $writtenOff  = EmployeeLoan::where('status', 'written_off')->whereBetween('updated_at', [$from, $to])->sum('remaining_balance');

        $totalDeductions = $totalExpenses + $salaries + $donations + $writtenOff;
        $netProfit       = $grossProfit - $totalDeductions;

        return view('reports.profit-loss', compact(
            'revenue', 'cogs', 'grossProfit', 'expenses', 'totalExpenses',
            'salaries', 'donations', 'writtenOff', 'totalDeductions', 'netProfit', 'from', 'to'
        ))->with('currencySymbol', $this->currencySymbol());
    }

    // -------------------------------------------------------------------------
    // 8. Tax Report
    // -------------------------------------------------------------------------

    public function tax(Request $request)
    {
        [$from, $to] = $this->parseDateRange($request);

        $taxByRate = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from, $to])
            ->selectRaw('SUM(sale_items.tax_amount) as total_tax, SUM(sale_items.total) as taxable_revenue, COUNT(DISTINCT sales.id) as sale_count')
            ->first();

        $totalTaxCollected = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->sum('tax_amount');

        $taxName = Setting::get('tax_name', 'Tax');
        $taxRate = Setting::get('tax_rate', 0);

        return view('reports.tax', compact(
            'taxByRate', 'totalTaxCollected', 'taxName', 'taxRate', 'from', 'to'
        ))->with('currencySymbol', $this->currencySymbol());
    }

    // -------------------------------------------------------------------------
    // 9. CSV Export
    // -------------------------------------------------------------------------

    public function export(Request $request, string $type): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        [$from, $to] = $this->parseDateRange($request);

        $allowedTypes = ['sales', 'expenses', 'employees', 'loans', 'profit-loss', 'tax'];

        if (!in_array($type, $allowedTypes)) {
            abort(404, "Unknown report type: {$type}");
        }

        [$headers, $rows] = match ($type) {
            'sales'       => $this->exportSalesData($from, $to),
            'expenses'    => $this->exportExpensesData($from, $to),
            'employees'   => $this->exportEmployeesData($from, $to),
            'loans'       => $this->exportLoansData(),
            'profit-loss' => $this->exportProfitLossData($from, $to),
            'tax'         => $this->exportTaxData($from, $to),
        };

        $filename = "{$type}-{$from->toDateString()}-to-{$to->toDateString()}.csv";
        $callback = function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ─── CSV Data Builders ────────────────────────────────────────────────────

    private function exportSalesData(Carbon $from, Carbon $to): array
    {
        $sales = Sale::with(['user', 'customer', 'payments'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        $headers = ['ID', 'Date', 'Customer', 'Cashier', 'Subtotal', 'Tax', 'Discount', 'Total', 'Payment Methods', 'Status'];

        $rows = $sales->map(fn ($s) => [
            $s->id,
            $s->created_at->toDateTimeString(),
            $s->customer?->name ?? 'Walk-in',
            $s->user?->name,
            number_format($s->subtotal_amount / 100, 2),
            number_format($s->tax_amount / 100, 2),
            number_format($s->discount_amount / 100, 2),
            number_format($s->final_amount / 100, 2),
            $s->payments->pluck('payment_method')->unique()->join(', '),
            $s->status,
        ])->toArray();

        return [$headers, $rows];
    }

    private function exportExpensesData(Carbon $from, Carbon $to): array
    {
        $expenses = Expense::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->get();

        $headers = ['ID', 'Date', 'Category', 'Description', 'Amount', 'Payment Method', 'Recurring'];

        $rows = $expenses->map(fn ($e) => [
            $e->id,
            $e->date->toDateString(),
            $e->category,
            $e->description,
            number_format($e->amount / 100, 2),
            $e->payment_method,
            $e->is_recurring ? 'Yes' : 'No',
        ])->toArray();

        return [$headers, $rows];
    }

    private function exportEmployeesData(Carbon $from, Carbon $to): array
    {
        $headers = ['ID', 'Name', 'Role', 'Sales Count', 'Sales Revenue', 'Hours Worked'];

        $employees = Employee::where('is_active', true)->with('user')->get();

        $rows = $employees->map(function (Employee $employee) use ($from, $to) {
            $salesData    = null;
            if ($employee->user_id) {
                $salesData = Sale::where('user_id', $employee->user_id)
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$from, $to])
                    ->selectRaw('COUNT(*) as count, SUM(final_amount) as revenue')
                    ->first();
            }

            $totalMinutes = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->sum('duration_minutes');

            return [
                $employee->id,
                $employee->name,
                $employee->role,
                $salesData?->count ?? 0,
                number_format(($salesData?->revenue ?? 0) / 100, 2),
                round($totalMinutes / 60, 2),
            ];
        })->toArray();

        return [$headers, $rows];
    }

    private function exportLoansData(): array
    {
        $headers = ['ID', 'Employee', 'Amount', 'Remaining Balance', 'Source', 'Status', 'Issued Date'];

        $rows = EmployeeLoan::with('employee')->get()->map(fn ($l) => [
            $l->id,
            $l->employee?->name,
            number_format($l->amount / 100, 2),
            number_format($l->remaining_balance / 100, 2),
            $l->source_type,
            $l->status,
            $l->created_at->toDateString(),
        ])->toArray();

        return [$headers, $rows];
    }

    private function exportProfitLossData(Carbon $from, Carbon $to): array
    {
        $headers = ['Line Item', 'Amount'];

        $revenue     = Sale::where('status', 'completed')->whereBetween('created_at', [$from, $to])->sum('final_amount');
        $cogs        = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from, $to])
            ->selectRaw('SUM(sale_items.cost_price * sale_items.quantity) as total')
            ->value('total') ?? 0;
        $expenses    = Expense::whereBetween('date', [$from->toDateString(), $to->toDateString()])->sum('amount');
        $salaries    = SalaryPayment::whereBetween('paid_date', [$from->toDateString(), $to->toDateString()])->sum('amount');
        $donations   = Donation::where('status', 'given')->whereBetween('given_date', [$from->toDateString(), $to->toDateString()])->sum('amount');
        $writtenOff  = EmployeeLoan::where('status', 'written_off')->whereBetween('updated_at', [$from, $to])->sum('remaining_balance');
        $netProfit   = $revenue - $cogs - $expenses - $salaries - $donations - $writtenOff;

        $rows = [
            ['Revenue',           number_format($revenue / 100, 2)],
            ['COGS',              number_format($cogs / 100, 2)],
            ['Gross Profit',      number_format(($revenue - $cogs) / 100, 2)],
            ['Expenses',          number_format($expenses / 100, 2)],
            ['Salaries',          number_format($salaries / 100, 2)],
            ['Donations',         number_format($donations / 100, 2)],
            ['Written-off Loans', number_format($writtenOff / 100, 2)],
            ['Net Profit',        number_format($netProfit / 100, 2)],
        ];

        return [$headers, $rows];
    }

    private function exportTaxData(Carbon $from, Carbon $to): array
    {
        $headers = ['Date', 'Sale ID', 'Tax Amount'];

        $rows = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($s) => [
                $s->created_at->toDateString(),
                $s->id,
                number_format($s->tax_amount / 100, 2),
            ])->toArray();

        return [$headers, $rows];
    }
}
