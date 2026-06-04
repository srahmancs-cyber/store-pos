<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('sales');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $customers = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:customers,email',
        ]);

        if (!$request->filled('name') && !$request->filled('phone') && !$request->filled('email')) {
            return back()->withErrors(['name' => 'Provide at least a name, phone, or email.'])->withInput();
        }

        $customer = Customer::create($request->only('name', 'phone', 'email'));

        return redirect()->route('customers.index')->with('success', "Customer '{$customer->name}' added.");
    }

    public function show(Customer $customer)
    {
        $customer->load(['sales' => fn ($q) => $q->latest()->take(20)]);
        $sym = Setting::get('currency_symbol', '$');
        $totalSpent = $customer->sales->where('status', 'completed')->sum('final_amount');

        return view('customers.show', compact('customer', 'sym', 'totalSpent'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name'  => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
        ]);

        $customer->update($request->only('name', 'phone', 'email'));

        return redirect()->route('customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete customer with sales history.']);
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }
}
