@extends('layouts.app')
@section('title', 'New Stock Adjustment')
@section('breadcrumb')
    <a href="{{ route('inventory.adjustments.index') }}" class="text-gray-500 hover:text-gray-900">Stock Adjustments</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">New Adjustment</span>
@endsection
@section('content')
<div class="pt-6 max-w-lg">
    <h1 class="mb-6">New Stock Adjustment</h1>
    <form method="POST" action="{{ route('inventory.adjustments.store') }}" class="space-y-5">
        @csrf
        <div class="card">
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Product <span class="text-red-500">*</span></label>
                    <select name="product_id" class="form-select" required>
                        <option value="">Select product</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} — Stock: {{ $p->current_stock }}
                        </option>
                        @endforeach
                    </select>
                    @error('product_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Adjustment Type <span class="text-red-500">*</span></label>
                        <select name="adjustment_type" class="form-select" required>
                            <option value="add" {{ old('adjustment_type')==='add'?'selected':'' }}>Add Stock</option>
                            <option value="remove" {{ old('adjustment_type')==='remove'?'selected':'' }}>Remove Stock</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" class="form-input" min="1" max="999999"
                            value="{{ old('quantity') }}" required>
                        @error('quantity')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="form-label">Reason <span class="text-red-500">*</span></label>
                    <select name="reason" class="form-select" required>
                        <option value="">Select reason</option>
                        @foreach(['damaged'=>'Damaged','lost'=>'Lost / Theft','recount'=>'Stock Recount'] as $v=>$l)
                        <option value="{{ $v }}" {{ old('reason')===$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                    @error('reason')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-input" rows="2" maxlength="500">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="{{ route('inventory.adjustments.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Record Adjustment</button>
        </div>
    </form>
</div>
@endsection
