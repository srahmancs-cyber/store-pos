@extends('layouts.app')
@section('title', 'Receive Stock — PO #{{ $purchaseOrder->id }}')
@section('breadcrumb')
    <a href="{{ route('inventory.purchase-orders.index') }}" class="text-gray-500 hover:text-gray-900">Purchase Orders</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <a href="{{ route('inventory.purchase-orders.show', $purchaseOrder) }}" class="text-gray-500 hover:text-gray-900">PO #{{ $purchaseOrder->id }}</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Receive Stock</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 max-w-3xl space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1>Receive Stock</h1>
            <p class="text-sm text-gray-500 mt-1">PO #{{ $purchaseOrder->id }} — {{ $purchaseOrder->supplier?->name }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('inventory.purchase-orders.receive', $purchaseOrder) }}">
        @csrf
        <div class="card">
            <div class="table-wrapper">
                <table class="table">
                    <thead><tr>
                        <th>Product</th>
                        <th>Ordered</th>
                        <th>Previously Received</th>
                        <th>Remaining</th>
                        <th>Receive Now</th>
                        <th>Unit Cost</th>
                    </tr></thead>
                    <tbody>
                        @foreach($purchaseOrder->items as $item)
                        @php $remaining = $item->ordered_quantity - ($item->received_quantity ?? 0); @endphp
                        <tr>
                            <td class="font-medium">{{ $item->product?->name }}</td>
                            <td>{{ $item->ordered_quantity }}</td>
                            <td class="text-gray-500">{{ $item->received_quantity ?? 0 }}</td>
                            <td>
                                @if($remaining > 0)
                                    <span class="badge badge-yellow">{{ $remaining }}</span>
                                @else
                                    <span class="badge badge-green">Complete</span>
                                @endif
                            </td>
                            <td class="w-28">
                                <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                <input type="number" name="items[{{ $loop->index }}][received_quantity]"
                                    min="0" max="{{ $remaining }}"
                                    value="{{ $remaining }}"
                                    class="form-input w-24"
                                    {{ $remaining <= 0 ? 'disabled' : '' }}>
                            </td>
                            <td>{{ $sym }}{{ number_format($item->unit_cost/100,2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('inventory.purchase-orders.show', $purchaseOrder) }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <i data-lucide="package-check" class="w-4 h-4"></i> Confirm Receipt
            </button>
        </div>
    </form>
</div>
@endsection
