@extends('layouts.app')
@section('title','Stock Adjustments')
@section('breadcrumb')
    <span class="text-gray-500">Inventory</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Stock Adjustments</span>
@endsection
@section('content')
<div class="pt-6 space-y-6">
    <h1>Stock Adjustments</h1>

    {{-- New Adjustment Form --}}
    <div class="card">
        <div class="card-header"><h3>New Adjustment</h3></div>
        <form method="POST" action="{{ route('inventory.adjustments.store') }}">
            @csrf
            <div class="card-body grid grid-cols-4 gap-4 items-end">
                <div>
                    <label class="form-label">Product <span class="text-red-500">*</span></label>
                    <select name="product_id" class="form-select" required>
                        <option value="">Select product</option>
                        @foreach($products as $prod)
                        <option value="{{ $prod->id }}" {{ old('product_id') == $prod->id ? 'selected' : '' }}>
                            {{ $prod->name }} ({{ $prod->current_stock }})
                        </option>
                        @endforeach
                    </select>
                    @error('product_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Type <span class="text-red-500">*</span></label>
                    <select name="adjustment_type" class="form-select" required>
                        <option value="add" {{ old('adjustment_type') === 'add' ? 'selected' : '' }}>Add Stock</option>
                        <option value="remove" {{ old('adjustment_type') === 'remove' ? 'selected' : '' }}>Remove Stock</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity" class="form-input" min="1" max="999999"
                        value="{{ old('quantity') }}" required>
                    @error('quantity')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Reason <span class="text-red-500">*</span></label>
                    <select name="reason" class="form-select" required>
                        <option value="">Select reason</option>
                        @foreach(['damaged'=>'Damaged','lost'=>'Lost/Theft','recount'=>'Stock Recount','expired'=>'Expired'] as $val => $label)
                        <option value="{{ $val }}" {{ old('reason') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('reason')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="px-6 pb-5 flex justify-end">
                <button type="submit" class="btn-primary">Record Adjustment</button>
            </div>
        </form>
    </div>

    {{-- History --}}
    <div class="card">
        <div class="card-header"><h3>Adjustment History</h3></div>
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Qty</th>
                    <th>Before</th>
                    <th>After</th>
                    <th>Reason</th>
                    <th>By</th>
                </tr></thead>
                <tbody>
                    @forelse($adjustments as $log)
                    <tr>
                        <td class="text-gray-500 text-xs">{{ $log->created_at->format('M d, Y H:i') }}</td>
                        <td class="font-medium">{{ $log->product?->name }}</td>
                        <td>
                            @if($log->adjustment_type === 'add')
                                <span class="badge badge-green">Add</span>
                            @else
                                <span class="badge badge-red">Remove</span>
                            @endif
                        </td>
                        <td>{{ $log->quantity }}</td>
                        <td class="text-gray-500">{{ $log->old_quantity }}</td>
                        <td class="font-medium">{{ $log->new_quantity }}</td>
                        <td class="text-gray-500 capitalize">{{ str_replace('_',' ',$log->reason) }}</td>
                        <td class="text-gray-500">{{ $log->user?->name }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-gray-400 py-6">No adjustments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($adjustments->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $adjustments->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
