<?php

namespace App\Http\Controllers\Finances;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $query = Bill::with('creator')->latest('due_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('due_date', '<=', $request->date_to);
        }

        $bills      = $query->paginate(20)->withQueryString();
        $totalUnpaid = Bill::where('status', 'unpaid')->sum('amount');
        $overdueCount = Bill::where('status', 'unpaid')->where('due_date', '<', Carbon::today())->count();

        return view('finances.bills.index', compact('bills', 'totalUnpaid', 'overdueCount'));
    }

    public function create()
    {
        return view('finances.bills.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0.01',
            'due_date'    => 'required|date',
        ]);

        $bill = Bill::create([
            'description' => $request->description,
            'amount'      => Money::toCents($request->amount),
            'due_date'    => $request->due_date,
            'status'      => 'unpaid',
            'created_by'  => Auth::id(),
        ]);

        ActivityLogger::log('bill_create', "Bill created: '{$bill->description}' — " . Money::format($bill->amount), Bill::class, $bill->id);

        return redirect()->route('finances.bills.index')->with('success', 'Bill created.');
    }

    public function show(Bill $bill)
    {
        return view('finances.bills.show', compact('bill'));
    }

    public function edit(Bill $bill)
    {
        if ($bill->status === 'paid') {
            return back()->withErrors(['error' => 'Cannot edit a paid bill.']);
        }

        return view('finances.bills.edit', compact('bill'));
    }

    public function update(Request $request, Bill $bill)
    {
        if ($bill->status === 'paid') {
            return back()->withErrors(['error' => 'Cannot edit a paid bill.']);
        }

        $request->validate([
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0.01',
            'due_date'    => 'required|date',
        ]);

        $bill->update([
            'description' => $request->description,
            'amount'      => Money::toCents($request->amount),
            'due_date'    => $request->due_date,
        ]);

        ActivityLogger::log('bill_update', "Bill updated: '{$bill->description}'", Bill::class, $bill->id);

        return redirect()->route('finances.bills.index')->with('success', 'Bill updated.');
    }

    public function destroy(Bill $bill)
    {
        ActivityLogger::log('bill_delete', "Bill deleted: '{$bill->description}'", Bill::class, $bill->id);

        $bill->delete();

        return redirect()->route('finances.bills.index')->with('success', 'Bill deleted.');
    }

    public function markPaid(Bill $bill)
    {
        if ($bill->status === 'paid') {
            return back()->withErrors(['error' => 'Bill is already marked as paid.']);
        }

        $request = request();
        $paymentMethod = $request->input('payment_method', 'bank');

        $bill->update([
            'status'         => 'paid',
            'paid_date'      => Carbon::today(),
            'payment_method' => $paymentMethod,
        ]);

        // Deduct from the relevant balance
        if ($paymentMethod === 'cash') {
            $balance = (int) \App\Models\Setting::get('cash_balance', 0);
            \App\Models\Setting::set('cash_balance', $balance - $bill->amount, 'integer', 'finance');
        } else {
            $balance = (int) \App\Models\Setting::get('bank_balance', 0);
            \App\Models\Setting::set('bank_balance', $balance - $bill->amount, 'integer', 'finance');
        }

        ActivityLogger::log('bill_paid', "Bill marked paid: '{$bill->description}' via {$paymentMethod}", Bill::class, $bill->id);

        return redirect()->route('finances.bills.index')->with('success', 'Bill marked as paid.');
    }
}
