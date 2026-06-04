@extends('layouts.app')
@section('title', 'Edit Purchase Order')
@section('breadcrumb')
    <a href="{{ route('inventory.purchase-orders.index') }}" class="text-gray-500 hover:text-gray-900">Purchase Orders</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Edit PO #{{ $purchaseOrder->id }}</span>
@endsection
@section('content')
<div class="pt-6 max-w-xl space-y-5">
    <h1>Edit Purchase Order #{{ $purchaseOrder->id }}</h1>
    @if($purchaseOrder->status === 'received')
    <div class="alert alert-warning">
        <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0"></i>
        This purchase order has already been received and cannot be edited.
    </div>
    @else
    <form method="POST" action="{{ route('inventory.purchase-orders.update', $purchaseOrder) }}" class="space-y-5">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select" required>
                        @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ $purchaseOrder->supplier_id == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Order Date</label>
                    <input type="date" name="order_date" class="form-input"
                        value="{{ old('order_date', \Carbon\Carbon::parse($purchaseOrder->order_date)->toDateString()) }}" required>
                </div>
                <div>
                    <label class="form-label">Expected Delivery</label>
                    <input type="date" name="expected_delivery" class="form-input"
                        value="{{ old('expected_delivery', $purchaseOrder->expected_delivery ? \Carbon\Carbon::parse($purchaseOrder->expected_delivery)->toDateString() : '') }}">
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-input" value="{{ old('notes', $purchaseOrder->notes) }}">
                </div>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="{{ route('inventory.purchase-orders.show', $purchaseOrder) }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
    @endif
</div>
@endsection
