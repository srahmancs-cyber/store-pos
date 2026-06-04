<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('purchaseOrders')
            ->orderBy('name')
            ->paginate(20);

        return view('inventory.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('inventory.suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'contact_person'  => 'nullable|string|max:255',
            'email'           => 'nullable|email|max:255|unique:suppliers,email',
            'phone'           => 'nullable|string|max:50',
            'address'         => 'nullable|string|max:500',
            'lead_time_days'  => 'nullable|integer|min:0',
            'payment_terms'   => 'nullable|string|max:255',
        ]);

        $supplier = Supplier::create(array_merge(
            $request->only('name', 'contact_person', 'email', 'phone', 'address', 'lead_time_days', 'payment_terms'),
            ['is_active' => true]
        ));

        ActivityLogger::log('supplier_create', "Supplier '{$supplier->name}' created", Supplier::class, $supplier->id);

        return redirect()->route('inventory.suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['purchaseOrders' => fn ($q) => $q->latest()->take(10)]);

        return view('inventory.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('inventory.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255|unique:suppliers,email,' . $supplier->id,
            'phone'          => 'nullable|string|max:50',
            'address'        => 'nullable|string|max:500',
            'lead_time_days' => 'nullable|integer|min:0',
            'payment_terms'  => 'nullable|string|max:255',
            'is_active'      => 'boolean',
        ]);

        $supplier->update($request->only(
            'name', 'contact_person', 'email', 'phone', 'address',
            'lead_time_days', 'payment_terms', 'is_active'
        ));

        ActivityLogger::log('supplier_update', "Supplier '{$supplier->name}' updated", Supplier::class, $supplier->id);

        return redirect()->route('inventory.suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseOrders()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete supplier with associated purchase orders.']);
        }

        ActivityLogger::log('supplier_delete', "Supplier '{$supplier->name}' deleted", Supplier::class, $supplier->id);

        $supplier->delete();

        return redirect()->route('inventory.suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
