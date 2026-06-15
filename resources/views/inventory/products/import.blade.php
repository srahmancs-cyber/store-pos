@extends('layouts.app')
@section('title', 'Import Products')
@section('breadcrumb')
    <a href="{{ route('inventory.products.index') }}" class="text-gray-500 hover:text-gray-900">Products</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Import</span>
@endsection

@section('content')
<div class="pt-6 max-w-2xl space-y-6">

    <div class="flex items-center justify-between">
        <h1>Import Products</h1>
        <a href="{{ route('inventory.products.import-template') }}" class="btn-secondary">
            <i data-lucide="download" class="w-4 h-4"></i> Download Template
        </a>
    </div>

    {{-- How it works --}}
    <div class="card">
        <div class="card-header"><h3>How It Works</h3></div>
        <div class="card-body space-y-3 text-sm text-gray-600">
            <div class="flex gap-3">
                <span class="flex-shrink-0 w-6 h-6 bg-gray-900 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                <p>Download the CSV template above. It contains all required columns with an example row.</p>
            </div>
            <div class="flex gap-3">
                <span class="flex-shrink-0 w-6 h-6 bg-gray-900 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                <p>Fill in your products. <strong>SKU is the unique key</strong> — existing SKUs will be <span class="text-blue-600 font-medium">updated</span>, new SKUs will be <span class="text-green-600 font-medium">created</span>.</p>
            </div>
            <div class="flex gap-3">
                <span class="flex-shrink-0 w-6 h-6 bg-gray-900 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                <p>Upload the file. You'll see a preview with any errors before anything is saved.</p>
            </div>
            <div class="flex gap-3">
                <span class="flex-shrink-0 w-6 h-6 bg-gray-900 text-white rounded-full flex items-center justify-center text-xs font-bold">4</span>
                <p>Review and confirm. Only rows without errors will be imported.</p>
            </div>
        </div>
    </div>

    {{-- Column reference --}}
    <div class="card">
        <div class="card-header"><h3>Column Reference</h3></div>
        <div class="table-wrapper">
            <table class="table text-xs">
                <thead><tr><th>Column</th><th>Required</th><th>Notes</th></tr></thead>
                <tbody>
                    <tr><td class="font-mono">name</td><td><span class="badge badge-red">Yes</span></td><td>Product display name</td></tr>
                    <tr><td class="font-mono">sku</td><td><span class="badge badge-red">Yes</span></td><td>Unique identifier. Existing SKU = update, new SKU = create</td></tr>
                    <tr><td class="font-mono">barcode</td><td><span class="badge badge-gray">No</span></td><td>Barcode / EAN / UPC (optional)</td></tr>
                    <tr><td class="font-mono">category</td><td><span class="badge badge-gray">No</span></td><td>Category name. Auto-created if it doesn't exist</td></tr>
                    <tr><td class="font-mono">supplier</td><td><span class="badge badge-gray">No</span></td><td>Supplier name. Must already exist in Inventory → Suppliers</td></tr>
                    <tr><td class="font-mono">cost_price</td><td><span class="badge badge-red">Yes</span></td><td>Decimal, e.g. <code>10.50</code></td></tr>
                    <tr><td class="font-mono">selling_price</td><td><span class="badge badge-red">Yes</span></td><td>Decimal, e.g. <code>19.99</code></td></tr>
                    <tr><td class="font-mono">current_stock</td><td><span class="badge badge-red">Yes</span></td><td>Whole number. <strong>Only used for new products</strong> — updates ignore this field</td></tr>
                    <tr><td class="font-mono">reorder_point</td><td><span class="badge badge-red">Yes</span></td><td>Whole number. Alert threshold</td></tr>
                    <tr><td class="font-mono">is_active</td><td><span class="badge badge-red">Yes</span></td><td><code>1</code> = active, <code>0</code> = inactive</td></tr>
                    <tr><td class="font-mono">has_serial</td><td><span class="badge badge-red">Yes</span></td><td><code>1</code> = track serial numbers, <code>0</code> = don't</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Upload form --}}
    <div class="card">
        <div class="card-header"><h3>Upload CSV File</h3></div>
        <form method="POST" action="{{ route('inventory.products.import.preview') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">CSV File <span class="text-red-500">*</span></label>
                    <input type="file" name="csv_file" accept=".csv,.txt"
                        class="form-input @error('csv_file') border-red-400 @enderror" required>
                    <p class="text-xs text-gray-400 mt-1">Max 5MB. UTF-8 encoded CSV.</p>
                    @error('csv_file')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="px-6 pb-5 flex gap-3 justify-end border-t border-gray-100 pt-4">
                <a href="{{ route('inventory.products.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    <i data-lucide="upload" class="w-4 h-4"></i> Upload & Preview
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
