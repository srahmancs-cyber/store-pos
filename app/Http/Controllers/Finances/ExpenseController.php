<?php

namespace App\Http\Controllers\Finances;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('creator')->latest('date');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('is_recurring')) {
            $query->where('is_recurring', $request->boolean('is_recurring'));
        }

        $totalAmount = (clone $query)->sum('amount');
        $expenses    = $query->paginate(25)->withQueryString();
        $categories  = Expense::distinct()->pluck('category')->sort()->values();

        return view('finances.expenses.index', compact('expenses', 'categories', 'totalAmount'));
    }

    public function create()
    {
        $categories = Expense::distinct()->pluck('category')->sort()->values();

        return view('finances.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category'               => 'required|string|max:100',
            'amount'                 => 'required|numeric|min:0.01',
            'description'            => 'nullable|string|max:500',
            'date'                   => 'required|date',
            'payment_method'         => 'nullable|in:cash,bank,card',
            'receipt_image'          => 'nullable|image|max:2048',
            'is_recurring'           => 'boolean',
            'recurring_day_of_month' => 'nullable|integer|min:1|max:31',
        ]);

        $data = $request->only('category', 'description', 'date', 'payment_method');
        $data['amount']                 = Money::toCents($request->amount);
        $data['is_recurring']           = $request->boolean('is_recurring');
        $data['recurring_day_of_month'] = $data['is_recurring'] ? $request->recurring_day_of_month : null;
        $data['created_by']             = Auth::id();

        if ($request->hasFile('receipt_image')) {
            $data['receipt_image'] = $request->file('receipt_image')->store('receipts', 'public');
        }

        $expense = Expense::create($data);

        // Deduct from cash or bank balance
        if ($expense->payment_method === 'cash') {
            $balance = (int) Setting::get('cash_balance', 0);
            Setting::set('cash_balance', $balance - $expense->amount, 'integer', 'finance');
        } elseif ($expense->payment_method === 'bank') {
            $balance = (int) Setting::get('bank_balance', 0);
            Setting::set('bank_balance', $balance - $expense->amount, 'integer', 'finance');
        }

        ActivityLogger::log('expense_create', "Expense recorded: {$expense->category} — " . Money::format($expense->amount), Expense::class, $expense->id);

        return redirect()->route('finances.expenses.index')->with('success', 'Expense recorded.');
    }

    public function show(Expense $expense)
    {
        $expense->load(['creator', 'children']);

        return view('finances.expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $categories = Expense::distinct()->pluck('category')->sort()->values();

        return view('finances.expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'category'               => 'required|string|max:100',
            'amount'                 => 'required|numeric|min:0.01',
            'description'            => 'nullable|string|max:500',
            'date'                   => 'required|date',
            'payment_method'         => 'nullable|in:cash,bank,card',
            'receipt_image'          => 'nullable|image|max:2048',
            'is_recurring'           => 'boolean',
            'recurring_day_of_month' => 'nullable|integer|min:1|max:31',
        ]);

        $data = $request->only('category', 'description', 'date', 'payment_method');
        $data['amount']                 = Money::toCents($request->amount);
        $data['is_recurring']           = $request->boolean('is_recurring');
        $data['recurring_day_of_month'] = $data['is_recurring'] ? $request->recurring_day_of_month : null;

        if ($request->hasFile('receipt_image')) {
            if ($expense->receipt_image) {
                Storage::disk('public')->delete($expense->receipt_image);
            }
            $data['receipt_image'] = $request->file('receipt_image')->store('receipts', 'public');
        }

        $expense->update($data);

        ActivityLogger::log('expense_update', "Expense updated: {$expense->category}", Expense::class, $expense->id);

        return redirect()->route('finances.expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_image) {
            Storage::disk('public')->delete($expense->receipt_image);
        }

        ActivityLogger::log('expense_delete', "Expense deleted: {$expense->category} — " . Money::format($expense->amount), Expense::class, $expense->id);

        $expense->delete();

        return redirect()->route('finances.expenses.index')->with('success', 'Expense deleted.');
    }
}
