<?php

namespace App\Http\Controllers\Employees;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\LoanRepayment;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeLoan::with(['employee', 'repayments'])->latest();

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $loans     = $query->paginate(20)->withQueryString();
        $employees = Employee::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('employees.loans.index', compact('loans', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'         => 'required|integer|exists:employees,id',
            'amount'              => 'required|numeric|min:0.01',
            'reason'              => 'nullable|string|max:500',
            'source_type'         => 'required|in:cash,bank',
            'auto_deduct'         => 'boolean',
            'auto_deduct_amount'  => 'nullable|numeric|min:0',
        ]);

        $amountCents = Money::toCents($request->amount);

        DB::transaction(function () use ($request, $amountCents) {
            $employee = Employee::findOrFail($request->employee_id);

            // Deduct from source balance
            $balanceKey = $request->source_type === 'cash' ? 'cash_balance' : 'bank_balance';
            $balance    = (int) Setting::get($balanceKey, 0);

            if ($balance < $amountCents) {
                throw new \InvalidArgumentException(
                    "Insufficient {$request->source_type} balance to issue loan."
                );
            }

            Setting::set($balanceKey, $balance - $amountCents, 'integer', 'finance');

            $loan = EmployeeLoan::create([
                'employee_id'        => $employee->id,
                'amount'             => $amountCents,
                'remaining_balance'  => $amountCents,
                'reason'             => $request->reason,
                'source_type'        => $request->source_type,
                'status'             => 'outstanding',
                'auto_deduct'        => $request->boolean('auto_deduct'),
                'auto_deduct_amount' => $request->filled('auto_deduct_amount') ? Money::toCents($request->auto_deduct_amount) : null,
                'approved_by'        => Auth::id(),
                'approved_at'        => now(),
            ]);

            ActivityLogger::log(
                'loan_create',
                "Loan of " . Money::format($amountCents) . " issued to '{$employee->name}'",
                EmployeeLoan::class,
                $loan->id
            );
        });

        return redirect()->route('employees.loans')->with('success', 'Loan created successfully.');
    }

    public function repay(Request $request, EmployeeLoan $loan)
    {
        if ($loan->status !== 'outstanding') {
            return back()->withErrors(['error' => 'Only outstanding loans can be repaid.']);
        }

        $request->validate([
            'amount'           => 'required|numeric|min:0.01',
            'destination_type' => 'required|in:cash,bank',
            'repayment_date'   => 'required|date',
            'notes'            => 'nullable|string|max:500',
        ]);

        $repaymentCents = Money::toCents($request->amount);

        if ($repaymentCents > $loan->remaining_balance) {
            return back()->withErrors(['amount' => 'Repayment amount exceeds remaining balance.'])->withInput();
        }

        DB::transaction(function () use ($request, $loan, $repaymentCents) {
            $repayment = LoanRepayment::create([
                'loan_id'          => $loan->id,
                'amount'           => $repaymentCents,
                'destination_type' => $request->destination_type,
                'repayment_date'   => $request->repayment_date,
                'notes'            => $request->notes,
                'created_by'       => Auth::id(),
            ]);

            $newBalance = $loan->remaining_balance - $repaymentCents;

            $loan->update([
                'remaining_balance' => $newBalance,
                'status'            => $newBalance <= 0 ? 'repaid' : 'outstanding',
            ]);

            // Add repayment to destination balance
            $balanceKey = $request->destination_type === 'cash' ? 'cash_balance' : 'bank_balance';
            $balance    = (int) Setting::get($balanceKey, 0);
            Setting::set($balanceKey, $balance + $repaymentCents, 'integer', 'finance');

            ActivityLogger::log(
                'loan_repayment',
                "Loan repayment of " . Money::format($repaymentCents) . " for employee #{$loan->employee_id}. " .
                "Remaining: " . Money::format($newBalance),
                LoanRepayment::class,
                $repayment->id
            );
        });

        return redirect()->route('employees.loans')->with('success', 'Repayment recorded.');
    }

    public function writeOff(EmployeeLoan $loan)
    {
        if ($loan->status !== 'outstanding') {
            return back()->withErrors(['error' => 'Only outstanding loans can be written off.']);
        }

        $loan->update(['status' => 'written_off']);

        ActivityLogger::log(
            'loan_write_off',
            "Loan #{$loan->id} written off. Remaining balance: " . Money::format($loan->remaining_balance),
            EmployeeLoan::class,
            $loan->id
        );

        return redirect()->route('employees.loans')->with('success', 'Loan written off.');
    }
}
