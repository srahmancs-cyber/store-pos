@extends('layouts.app')
@section('title', 'Products')
@section('breadcrumb')
    <span class="text-gray-500">Inventory</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="text-gray-900 font-medium">Products</span>
@endsection

@section('content')
<div class="pt-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1>Products</h1>
        <div class="flex gap-2">
            <a href="{{ route('inventory.products.export') }}" class="btn-secondary">
                <i data-lucide="download" class="w-4 h-4"></i> Export CSV
            </a>
            <a href="{{ route('inventory.products.import') }}" class="btn-secondary">
                <i data-lucide="upload" class="w-4 h-4"></i> Import CSV
            </a>
            <a href="{{ route('inventory.products.create') }}" class="btn-primary">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Product
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card">
        <div class="card-body flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-input" placeholder="Name, SKU, barcode…" value="{{ request('search') }}">
            </div>
            <div class="w-44">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-44">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 pb-0.5">
                <input type="checkbox" id="low_stock" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} class="rounded border-gray-300">
                <label for="low_stock" class="text-sm text-gray-700">Low Stock Only</label>
            </div>
            <button type="submit" class="btn-secondary">Filter</button>
            <a href="{{ route('inventory.products.index') }}" class="btn-secondary">Clear</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Cost</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="font-medium text-gray-900">{{ $product->name }}</div>
                            @if($product->barcode)
                            <div class="text-xs text-gray-400">{{ $product->barcode }}</div>
                            @endif
                        </td>
                        <td class="text-gray-500 font-mono text-xs">{{ $product->sku }}</td>
                        <td class="text-gray-500">{{ $product->category?->name ?? '—' }}</td>
                        <td>{{ \App\Models\Setting::get('currency_symbol','$') }}{{ number_format($product->cost_price/100,2) }}</td>
                        <td class="font-medium">{{ \App\Models\Setting::get('currency_symbol','$') }}{{ number_format($product->selling_price/100,2) }}</td>
                        <td>
                            @if($product->current_stock <= $product->reorder_point)
                                <span class="badge badge-red">{{ $product->current_stock }}</span>
                            @else
                                <span class="text-gray-700">{{ $product->current_stock }}</span>
                            @endif
                        </td>
                        <td>
                            @if($product->is_active)
                                <span class="badge badge-green">Active</span>
                            @else
                                <span class="badge badge-gray">Inactive</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('inventory.products.show', $product) }}" class="btn btn-secondary btn-sm">View</a>
                                <a href="{{ route('inventory.products.edit', $product) }}" class="btn btn-secondary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('inventory.products.destroy', $product) }}"
                                    onsubmit="return confirm('Delete {{ $product->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-gray-400 py-8">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $products->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
