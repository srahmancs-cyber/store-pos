@extends('layouts.app')
@section('title', 'Edit Product')
@section('breadcrumb')
    <span class="text-gray-500">Inventory</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <a href="{{ route('inventory.products.index') }}" class="text-gray-500 hover:text-gray-900">Products</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="text-gray-900 font-medium">Edit</span>
@endsection

@section('content')
<div class="pt-6 max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <h1>Edit Product</h1>
        <a href="{{ route('inventory.products.show', $product) }}" class="btn-secondary">View Product</a>
    </div>

    <form method="POST" action="{{ route('inventory.products.update', $product) }}" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')

        <div class="card">
            <div class="card-header"><h3>Basic Information</h3></div>
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input @error('name') border-red-400 @enderror"
                        value="{{ old('name', $product->name) }}" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">SKU <span class="text-red-500">*</span></label>
                        <input type="text" name="sku" class="form-input @error('sku') border-red-400 @enderror"
                            value="{{ old('sku', $product->sku) }}" required>
                        @error('sku')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" class="form-input"
                            value="{{ old('barcode', $product->barcode) }}">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">— No Category —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">— No Supplier —</option>
                            @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ old('supplier_id', $product->supplier_id) == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3>Pricing</h3>
                <span class="text-xs text-gray-400">Stock: {{ $product->current_stock }} — use Stock Adjustments to change</span>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Cost Price <span class="text-red-500">*</span></label>
                        <input type="number" name="cost_price" class="form-input"
                            value="{{ old('cost_price', number_format($product->cost_price/100,2,'.','')) }}" min="0" step="0.01" required>
                    </div>
                    <div>
                        <label class="form-label">Selling Price <span class="text-red-500">*</span></label>
                        <input type="number" name="selling_price" class="form-input"
                            value="{{ old('selling_price', number_format($product->selling_price/100,2,'.','')) }}" min="0" step="0.01" required>
                    </div>
                </div>
                <div>
                    <label class="form-label">Reorder Point</label>
                    <input type="number" name="reorder_point" class="form-input w-32"
                        value="{{ old('reorder_point', $product->reorder_point) }}" min="0" step="1">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Options</h3></div>
            <div class="card-body space-y-3">
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
                    <label for="is_active" class="text-sm">Active</label>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="has_serial" name="has_serial" value="1"
                        {{ old('has_serial', $product->has_serial) ? 'checked' : '' }} class="rounded border-gray-300">
                    <label for="has_serial" class="text-sm">Track serial numbers</label>
                </div>
                @if($product->image)
                <div class="flex items-center gap-3 mt-2">
                    <img src="{{ Storage::url($product->image) }}" class="w-16 h-16 object-cover rounded border border-gray-200">
                    <span class="text-xs text-gray-400">Current image</span>
                </div>
                @endif
                <div>
                    <label class="form-label">{{ $product->image ? 'Replace Image' : 'Product Image' }} <span class="text-xs text-gray-400">(optional)</span></label>
                    <input type="file" name="image" accept="image/*" class="form-input">
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('inventory.products.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
