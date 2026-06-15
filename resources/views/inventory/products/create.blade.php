@extends('layouts.app')
@section('title', 'Add Product')
@section('breadcrumb')
    <span class="text-gray-500">Inventory</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <a href="{{ route('inventory.products.index') }}" class="text-gray-500 hover:text-gray-900">Products</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="text-gray-900 font-medium">Add Product</span>
@endsection

@section('content')
<div class="pt-6 max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <h1>Add Product</h1>
    </div>

    <form method="POST" action="{{ route('inventory.products.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <div class="card">
            <div class="card-header"><h3>Basic Information</h3></div>
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input @error('name') border-red-400 @enderror"
                        value="{{ old('name') }}" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">SKU <span class="text-red-500">*</span></label>
                        <input type="text" name="sku" class="form-input @error('sku') border-red-400 @enderror"
                            value="{{ old('sku') }}" required>
                        @error('sku')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" class="form-input @error('barcode') border-red-400 @enderror"
                            value="{{ old('barcode') }}">
                        @error('barcode')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">— No Category —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">— No Supplier —</option>
                            @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Pricing & Stock</h3></div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Cost Price <span class="text-red-500">*</span></label>
                        <input type="number" name="cost_price" class="form-input @error('cost_price') border-red-400 @enderror"
                            value="{{ old('cost_price', '0.00') }}" min="0" step="0.01" required>
                        @error('cost_price')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Selling Price <span class="text-red-500">*</span></label>
                        <input type="number" name="selling_price" class="form-input @error('selling_price') border-red-400 @enderror"
                            value="{{ old('selling_price', '0.00') }}" min="0" step="0.01" required>
                        @error('selling_price')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Initial Stock <span class="text-red-500">*</span></label>
                        <input type="number" name="current_stock" class="form-input @error('current_stock') border-red-400 @enderror"
                            value="{{ old('current_stock', '0') }}" min="0" step="1" required>
                        @error('current_stock')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Reorder Point <span class="text-red-500">*</span></label>
                        <input type="number" name="reorder_point" class="form-input @error('reorder_point') border-red-400 @enderror"
                            value="{{ old('reorder_point', '5') }}" min="0" step="1" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Options</h3></div>
            <div class="card-body space-y-3">
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active', '1') ? 'checked' : '' }} class="rounded border-gray-300">
                    <label for="is_active" class="text-sm text-gray-700">Active (available for sale)</label>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="has_serial" name="has_serial" value="1"
                        {{ old('has_serial') ? 'checked' : '' }} class="rounded border-gray-300">
                    <label for="has_serial" class="text-sm text-gray-700">Track serial numbers</label>
                </div>
                <div class="mt-2">
                    <label class="form-label">Product Image <span class="text-xs text-gray-400">(optional, max 2MB)</span></label>
                    <input type="file" name="image" accept="image/*" class="form-input">
                    @error('image')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Consignment --}}
        <div class="card" x-data="{ isConsignment: {{ old('is_consignment') ? 'true' : 'false' }} }">
            <div class="card-header flex items-center justify-between">
                <h3>Consignment</h3>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_consignment" name="is_consignment" value="1"
                        x-model="isConsignment" class="rounded border-gray-300">
                    <label for="is_consignment" class="text-sm text-gray-700">This is a consignment product</label>
                </div>
            </div>
            <div x-show="isConsignment" x-cloak class="card-body space-y-4 border-t border-gray-100">
                <p class="text-xs text-gray-500">The vendor places this product in your store. You keep a commission percentage per sale.</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Consignment Vendor</label>
                        <select name="consignment_vendor_id" class="form-select">
                            <option value="">Select vendor</option>
                            @foreach(\App\Models\ConsignmentVendor::where('is_active',true)->orderBy('name')->get() as $cv)
                            <option value="{{ $cv->id }}" {{ old('consignment_vendor_id') == $cv->id ? 'selected' : '' }}>
                                {{ $cv->name }} ({{ $cv->default_commission_rate }}% default)
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Override Commission % <span class="text-xs text-gray-400">(optional)</span></label>
                        <input type="number" name="consignment_rate" step="0.01" min="0" max="100"
                            class="form-input" value="{{ old('consignment_rate') }}"
                            placeholder="Leave blank to use vendor default">
                    </div>
                </div>
                <div>
                    <label class="form-label">Override Basis <span class="text-xs text-gray-400">(optional)</span></label>
                    <select name="consignment_basis" class="form-select w-48">
                        <option value="">Use vendor default</option>
                        <option value="sale_price" {{ old('consignment_basis')==='sale_price'?'selected':'' }}>% of Sale Price</option>
                        <option value="profit" {{ old('consignment_basis')==='profit'?'selected':'' }}>% of Profit</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('inventory.products.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create Product</button>
        </div>
    </form>
</div>
@endsection
