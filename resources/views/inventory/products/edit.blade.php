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
@php $investors = \App\Models\Owner::where('type','investor')->where('is_active',true)->orderBy('name')->get(); @endphp
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

        {{-- Investor Funding --}}
        @if($investors->count() > 0)
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3>Investor Funding</h3>
                <span class="text-xs text-gray-400">Optional</span>
            </div>
            <div class="card-body space-y-3">
                <p class="text-xs text-gray-500">
                    Link this product to an investor if it was purchased using their funds.
                    The investor's total contribution is automatically calculated from all their linked products
                    as <strong>SUM(cost price × current stock)</strong>.
                </p>
                <div>
                    <label class="form-label">Funded by Investor</label>
                    <select name="investor_id" class="form-select w-64">
                        <option value="">— Not investor-funded —</option>
                        @foreach($investors as $inv)
                        <option value="{{ $inv->id }}"
                            {{ old('investor_id', $product->investor_id) == $inv->id ? 'selected' : '' }}>
                            {{ $inv->name }}
                        </option>
                        @endforeach
                    </select>
                    @if($product->investor)
                    <p class="text-xs text-gray-400 mt-1">
                        Currently linked to: <strong>{{ $product->investor->name }}</strong>
                    </p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Consignment --}}
        <div class="card" x-data="{ isConsignment: {{ old('is_consignment', $product->is_consignment) ? 'true' : 'false' }} }">
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
                            <option value="{{ $cv->id }}"
                                {{ old('consignment_vendor_id', $product->consignment_vendor_id) == $cv->id ? 'selected' : '' }}>
                                {{ $cv->name }} ({{ $cv->default_commission_rate }}% default)
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Override Commission % <span class="text-xs text-gray-400">(optional)</span></label>
                        <input type="number" name="consignment_rate" step="0.01" min="0" max="100"
                            class="form-input" value="{{ old('consignment_rate', $product->consignment_rate) }}"
                            placeholder="Leave blank to use vendor default">
                    </div>
                </div>
                <div>
                    <label class="form-label">Override Basis <span class="text-xs text-gray-400">(optional)</span></label>
                    <select name="consignment_basis" class="form-select w-48">
                        <option value="">Use vendor default</option>
                        <option value="sale_price" {{ old('consignment_basis', $product->consignment_basis)==='sale_price'?'selected':'' }}>% of Sale Price</option>
                        <option value="profit" {{ old('consignment_basis', $product->consignment_basis)==='profit'?'selected':'' }}>% of Profit</option>
                    </select>
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
