@extends('layouts.app')
@section('title','New Purchase Order')
@section('breadcrumb')
    <a href="{{ route('inventory.purchase-orders.index') }}" class="text-gray-500 hover:text-gray-900">Purchase Orders</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">New Order</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 max-w-3xl"
    x-data="{
        lines: [{ product_id: '', product_name: '', quantity: 1, unit_cost: 0 }],
        addLine() { this.lines.push({ product_id: '', product_name: '', quantity: 1, unit_cost: 0 }) },
        removeLine(i) { this.lines.splice(i,1) },
        get total() { return this.lines.reduce((s,l) => s + (parseFloat(l.unit_cost)||0) * (parseInt(l.quantity)||0), 0) }
    }">
    <div class="flex items-center justify-between mb-6">
        <h1>New Purchase Order</h1>
    </div>
    <form method="POST" action="{{ route('inventory.purchase-orders.store') }}" class="space-y-5">
        @csrf
        <div class="card">
            <div class="card-header"><h3>Order Details</h3></div>
            <div class="card-body grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Supplier <span class="text-red-500">*</span></label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Order Date <span class="text-red-500">*</span></label>
                    <input type="date" name="order_date" class="form-input" required value="{{ old('order_date', now()->toDateString()) }}">
                </div>
                <div>
                    <label class="form-label">Expected Delivery</label>
                    <input type="date" name="expected_delivery" class="form-input" value="{{ old('expected_delivery') }}">
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-input" value="{{ old('notes') }}">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3>Order Items</h3>
                <button type="button" @click="addLine()" class="btn btn-secondary btn-sm">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Line
                </button>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead><tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Cost</th>
                        <th>Line Total</th>
                        <th></th>
                    </tr></thead>
                    <tbody>
                        <template x-for="(line, index) in lines" :key="index">
                            <tr>
                                <td class="w-64">
                                    <select :name="`items[${index}][product_id]`" x-model="line.product_id" class="form-select" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $prod)
                                        <option value="{{ $prod->id }}" data-cost="{{ $prod->cost_price }}">{{ $prod->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="w-24">
                                    <input type="number" :name="`items[${index}][quantity]`" x-model.number="line.quantity"
                                        min="1" class="form-input" required>
                                </td>
                                <td class="w-32">
                                    <input type="number" :name="`items[${index}][unit_cost]`" x-model.number="line.unit_cost"
                                        min="0" step="0.01" class="form-input" required>
                                </td>
                                <td class="font-medium" x-text="'{{ $sym }}' + (line.unit_cost * line.quantity).toFixed(2)"></td>
                                <td>
                                    <button type="button" @click="removeLine(index)" class="text-red-400 hover:text-red-600" x-show="lines.length > 1">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-200">
                            <td colspan="3" class="px-4 py-3 text-right font-medium text-gray-700">Total</td>
                            <td class="px-4 py-3 font-bold" x-text="'{{ $sym }}' + total.toFixed(2)"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('inventory.purchase-orders.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create Purchase Order</button>
        </div>
    </form>
</div>
@endsection
