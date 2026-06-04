@extends('layouts.app')
@section('title','Purchase Orders')
@section('breadcrumb')
    <span class="text-gray-500">Inventory</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Purchase Orders</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1>Purchase Orders</h1>
        <a href="{{ route('inventory.purchase-orders.create') }}" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i> New Order
        </a>
    </div>
    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>PO #</th>
                    <th>Supplier</th>
                    <th>Order Date</th>
                    <th>Expected Delivery</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                    <tr>
                        <td class="font-mono font-medium">#{{ $po->id }}</td>
                        <td class="font-medium">{{ $po->supplier?->name }}</td>
                        <td class="text-gray-500">{{ \Carbon\Carbon::parse($po->order_date)->format('M d, Y') }}</td>
                        <td class="text-gray-500">{{ $po->expected_delivery ? \Carbon\Carbon::parse($po->expected_delivery)->format('M d, Y') : '—' }}</td>
                        <td>{{ $sym }}{{ number_format($po->total_amount/100,2) }}</td>
                        <td>
                            @if($po->status === 'pending') <span class="badge badge-yellow">Pending</span>
                            @elseif($po->status === 'partial') <span class="badge badge-blue">Partial</span>
                            @elseif($po->status === 'received') <span class="badge badge-green">Received</span>
                            @else <span class="badge badge-gray">{{ ucfirst($po->status) }}</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('inventory.purchase-orders.show', $po) }}" class="btn btn-secondary btn-sm">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-400 py-8">No purchase orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchaseOrders->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $purchaseOrders->links() }}</div>
        @endif
    </div>
</div>
@endsection
