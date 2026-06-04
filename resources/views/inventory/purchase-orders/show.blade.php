@extends('layouts.app')
@section('title','Purchase Order #{{ $purchaseOrder->id }}')
@section('breadcrumb')
    <a href="{{ route('inventory.purchase-orders.index') }}" class="text-gray-500 hover:text-gray-900">Purchase Orders</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">PO #{{ $purchaseOrder->id }}</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1>Purchase Order #{{ $purchaseOrder->id }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $purchaseOrder->supplier?->name }} — {{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('M d, Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($purchaseOrder->status !== 'received')
            <span class="badge badge-yellow">{{ ucfirst($purchaseOrder->status) }}</span>
            @else
            <span class="badge badge-green">Received</span>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Product</th>
                    <th>Ordered Qty</th>
                    <th>Received</th>
                    <th>Unit Cost</th>
                    <th>Line Total</th>
                    @if($purchaseOrder->status !== 'received')<th>Receive Qty</th>@endif
                </tr></thead>
                <tbody>
                    @foreach($purchaseOrder->items as $item)
                    <tr>
                        <td class="font-medium">{{ $item->product?->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->received_quantity ?? 0 }}</td>
                        <td>{{ $sym }}{{ number_format($item->unit_cost/100,2) }}</td>
                        <td>{{ $sym }}{{ number_format(($item->unit_cost * $item->quantity)/100,2) }}</td>
                        @if($purchaseOrder->status !== 'received')
                        <td>
                            <input type="number" name="received[{{ $item->id }}]"
                                form="receive-form"
                                min="0" max="{{ $item->quantity }}"
                                value="{{ $item->quantity - ($item->received_quantity ?? 0) }}"
                                class="form-input w-20">
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200">
                        <td colspan="{{ $purchaseOrder->status !== 'received' ? 4 : 3 }}" class="px-4 py-3 text-right font-medium">Total</td>
                        <td class="px-4 py-3 font-bold">{{ $sym }}{{ number_format($purchaseOrder->total_amount/100,2) }}</td>
                        @if($purchaseOrder->status !== 'received')<td></td>@endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($purchaseOrder->status !== 'received')
    <div class="flex justify-end gap-2">
        <a href="{{ route('inventory.purchase-orders.receive-form', $purchaseOrder) }}" class="btn-primary">
            <i data-lucide="package-check" class="w-4 h-4"></i>
            Receive Stock
        </a>
        <form method="POST" action="{{ route('inventory.purchase-orders.cancel', $purchaseOrder) }}"
            onsubmit="return confirm('Cancel this purchase order?')">
            @csrf
            <button class="btn-secondary">Cancel Order</button>
        </form>
    </div>
    @endif
</div>
@endsection
