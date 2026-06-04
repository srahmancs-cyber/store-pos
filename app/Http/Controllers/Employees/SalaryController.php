<?php

namespace App\Http\Controllers\Employees;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Sale;
use App\Models\SalaryPayment;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd   = Carbon::create($year, $month, 1)->endOfMonth();

        $employees = Employee::where('is_active', true)->with('salaryPayments')->orderBy('name')->get();

        $employeeData = $employees->map(function (Employee $employee) use ($month, $year, $periodStart, $periodEnd) {
            $calculatedSalary = $this->calculateSalary($employee, $periodStart, $periodEnd);
            $alreadyPaid      = $employee->salaryPayments
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->sum('amount');

            return [
                'employee'          => $employee,
                'calculated_salary' => $calculatedSalary,
                'already_paid'      => $alreadyPaid,
                'balance_due'       => max(0, $calculatedSalary - $alreadyPaid),
            ];
        });

        $payments = SalaryPayment::with('employee')
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->latest()
            ->get();

        return view('employees.salaries', compact('employeeData', 'payments', 'month', 'year'));
    }

    public function pay(Request $request)
    {
        $request->validate([
            'employee_id'     => 'required|integer|exists:employees,id',
            'amount'          => 'required|numeric|min:0.01',
            'payment_method'  => 'required|in:cash,bank',
            'period_month'    => 'required|integer|min:1|max:12',
            'period_year'     => 'required|integer|min:2000|max:2100',
            'paid_date'       => 'required|date',
            'notes'           => 'nullable|string|max:500',
        ]);

        $amountCents = Money::toCents($request->amount);

        DB::transaction(function () use ($request, $amountCents) {
            $employee = Employee::findOrFail($request->employee_id);

            // Deduct from the relevant balance
            $balanceKey = $request->payment_method === 'cash' ? 'cash_balance' : 'bank_balance';
            $balance    = (int) Setting::get($balanceKey, 0);

            Setting::set($balanceKey, $balance - $amountCents, 'integer', 'finance');

            // Warn if balance goes negative but allow it
            if ($balance < $amountCents) {
                session()->flash('warning', "Warning: {$request->payment_method} balance went below zero after this payment.");
            }

            $payment = SalaryPayment::create([
                'employee_id'    => $employee->id,
                'amount'         => $amountCents,
                'period_month'   => $request->period_month,
                'period_year'    => $request->period_year,
                'payment_method' => $request->payment_method,
                'paid_date'      => $request->paid_date,
                'notes'          => $request->notes,
                'created_by'     => Auth::id(),
            ]);

            ActivityLogger::log(
                'salary_payment',
                "Salary paid to '{$employee->name}': " . Money::format($amountCents) .
                " for {$request->period_month}/{$request->period_year}",
                SalaryPayment::class,
                $payment->id
            );
        });

        return redirect()->route('employees.salaries')->with('success', 'Salary payment recorded.');
    }

    // -------------------------------------------------------------------------
    // Internal salary calculation
    // -------------------------------------------------------------------------

    private function calculateSalary(Employee $employee, Carbon $periodStart, Carbon $periodEnd): int
    {
        return match ($employee->salary_type) {
            'fixed'      => $employee->salary_value,
            'hourly'     => $this->calculateHourly($employee, $periodStart, $periodEnd),
            'commission' => $this->calculateCommission($employee, $periodStart, $periodEnd),
            default      => $employee->salary_value,
        };
    }

    private function calculateHourly(Employee $employee, Carbon $periodStart, Carbon $periodEnd): int
    {
        $totalMinutes = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->sum('duration_minutes');

        $hours = $totalMinutes / 60;

        return (int) round($hours * $employee->salary_value);
    }

    private function calculateCommission(Employee $employee, Carbon $periodStart, Carbon $periodEnd): int
    {
        if (!$employee->user_id) {
            return 0;
        }

        $totalSales = Sale::where('user_id', $employee->user_id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$periodStart, $periodEnd->endOfDay()])
            ->sum('final_amount');

        return (int) round($totalSales * ($employee->salary_value / 10000));
    }
}
