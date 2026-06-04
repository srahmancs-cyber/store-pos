@extends('layouts.app')
@section('title', $category->name)
@section('breadcrumb')
    <a href="{{ route('inventory.categories.index') }}" class="text-gray-500 hover:text-gray-900">Categories</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">{{ $category->name }}</span>
@endsection
@section('content')
<div class="pt-6 space-y-5">
    <div class="flex items-center justify-between">
        <h1>{{ $category->name }}</h1>
        <a href="{{ route('inventory.categories.edit', $category) }}" class="btn-secondary">Edit</a>
    </div>
    <div class="grid grid-cols-3 gap-5">
        <div class="card col-span-1">
            <div class="card-header"><h3>Details</h3></div>
            <div class="card-body space-y-3 text-sm">
                <div><p class="text-gray-500">Parent</p><p class="font-medium mt-0.5">{{ $category->parent?->name ?? '— None —' }}</p></div>
                <div><p class="text-gray-500">Description</p><p class="mt-0.5">{{ $category->description ?? '—' }}</p></div>
                <div><p class="text-gray-500">Sub-categories</p><p class="font-medium mt-0.5">{{ $category->children->count() }}</p></div>
                <div><p class="text-gray-500">Products</p><p class="font-medium mt-0.5">{{ $category->products_count ?? $category->products->count() }}</p></div>
            </div>
        </div>
        <div class="col-span-2 card">
            <div class="card-header"><h3>Products in this Category</h3></div>
            <div class="table-wrapper">
                <table class="table">
                    <thead><tr><th>Name</th><th>SKU</th><th>Stock</th><th>Price</th></tr></thead>
                    <tbody>
                        @forelse($category->products->take(20) as $product)
                        <tr>
                            <td><a href="{{ route('inventory.products.show', $product) }}" class="font-medium hover:underline">{{ $product->name }}</a></td>
                            <td class="font-mono text-xs text-gray-500">{{ $product->sku }}</td>
                            <td>{{ $product->current_stock }}</td>
                            <td>{{ \App\Models\Setting::get('currency_symbol','$') }}{{ number_format($product->selling_price/100,2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-gray-400 py-6">No products in this category.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
