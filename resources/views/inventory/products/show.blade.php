@extends('layouts.app')
@section('title', $product->name)
@section('breadcrumb')
    <a href="{{ route('inventory.products.index') }}" class="text-gray-500 hover:text-gray-900">Products</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="text-gray-900 font-medium">{{ $product->name }}</span>
@endsection

@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1>{{ $product->name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('inventory.products.edit', $product) }}" class="btn-secondary">Edit</a>
            <a href="{{ route('inventory.adjustments.index') }}?product_id={{ $product->id }}" class="btn-primary">
                <i data-lucide="sliders" class="w-4 h-4"></i> Adjust Stock
            </a>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-5">
            <div class="card">
                <div class="card-header"><h3>Product Details</h3></div>
                <div class="card-body grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">SKU</span><p class="font-mono font-medium mt-0.5">{{ $product->sku }}</p></div>
                    <div><span class="text-gray-500">Barcode</span><p class="font-mono mt-0.5">{{ $product->barcode ?? '—' }}</p></div>
                    <div><span class="text-gray-500">Category</span><p class="mt-0.5">{{ $product->category?->name ?? '—' }}</p></div>
                    <div><span class="text-gray-500">Supplier</span><p class="mt-0.5">{{ $product->supplier?->name ?? '—' }}</p></div>
                    <div><span class="text-gray-500">Cost Price</span><p class="mt-0.5 font-medium">{{ $sym }}{{ number_format($product->cost_price/100,2) }}</p></div>
                    <div><span class="text-gray-500">Selling Price</span><p class="mt-0.5 font-medium">{{ $sym }}{{ number_format($product->selling_price/100,2) }}</p></div>
                    <div><span class="text-gray-500">Current Stock</span>
                        <p class="mt-0.5">
                            @if($product->current_stock <= $product->reorder_point)
                                <span class="badge badge-red">{{ $product->current_stock }}</span>
                            @else
                                <span class="font-medium">{{ $product->current_stock }}</span>
                            @endif
                        </p>
                    </div>
                    <div><span class="text-gray-500">Reorder Point</span><p class="mt-0.5">{{ $product->reorder_point }}</p></div>
                    <div><span class="text-gray-500">Serial Tracking</span><p class="mt-0.5">{{ $product->has_serial ? 'Yes' : 'No' }}</p></div>
                    <div><span class="text-gray-500">Status</span>
                        <p class="mt-0.5">
                            @if($product->is_active) <span class="badge badge-green">Active</span>
                            @else <span class="badge badge-gray">Inactive</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Inventory History --}}
            <div class="card">
                <div class="card-header"><h3>Inventory History</h3></div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead><tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Qty Change</th>
                            <th>Before</th>
                            <th>After</th>
                            <th>Reason</th>
                            <th>By</th>
                        </tr></thead>
                        <tbody>
                            @forelse($inventoryLogs as $log)
                            <tr>
                                <td class="text-gray-500 text-xs">{{ $log->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($log->adjustment_type === 'add')
                                        <span class="badge badge-green">+{{ $log->quantity }}</span>
                                    @else
                                        <span class="badge badge-red">-{{ $log->quantity }}</span>
                                    @endif
                                </td>
                                <td>{{ $log->adjustment_type === 'add' ? '+' : '-' }}{{ $log->quantity }}</td>
                                <td class="text-gray-500">{{ $log->old_quantity }}</td>
                                <td class="font-medium">{{ $log->new_quantity }}</td>
                                <td class="text-gray-500 capitalize">{{ str_replace('_',' ',$log->reason) }}</td>
                                <td class="text-gray-500">{{ $log->user?->name ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-gray-400 py-4">No history yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Side panel --}}
        <div class="space-y-4">
            @if($product->image)
            <div class="card overflow-hidden">
                <img src="{{ Storage::url($product->image) }}" class="w-full object-cover" style="max-height:200px">
            </div>
            @endif
            <div class="card">
                <div class="card-body text-sm space-y-2">
                    <p class="text-gray-500">Created</p>
                    <p>{{ $product->created_at->format('M d, Y') }}</p>
                    <p class="text-gray-500 mt-2">Last Updated</p>
                    <p>{{ $product->updated_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
