@extends('layouts.app')
@section('title', $supplier->name)
@section('breadcrumb')
    <a href="{{ route('inventory.suppliers.index') }}" class="text-gray-500 hover:text-gray-900">Suppliers</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">{{ $supplier->name }}</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 space-y-5">
    <div class="flex items-center justify-between">
        <h1>{{ $supplier->name }}</h1>
        <a href="{{ route('inventory.suppliers.edit', $supplier) }}" class="btn-secondary">Edit</a>
    </div>
    <div class="grid grid-cols-3 gap-5">
        <div class="card">
            <div class="card-header"><h3>Contact</h3></div>
            <div class="card-body space-y-3 text-sm">
                <div><p class="text-gray-500">Contact Person</p><p class="font-medium mt-0.5">{{ $supplier->contact_person ?? '—' }}</p></div>
                <div><p class="text-gray-500">Phone</p><p class="mt-0.5">{{ $supplier->phone ?? '—' }}</p></div>
                <div><p class="text-gray-500">Email</p><p class="mt-0.5">{{ $supplier->email ?? '—' }}</p></div>
                <div><p class="text-gray-500">Address</p><p class="mt-0.5">{{ $supplier->address ?? '—' }}</p></div>
                <div><p class="text-gray-500">Lead Time</p><p class="mt-0.5">{{ $supplier->lead_time_days ? $supplier->lead_time_days.' days' : '—' }}</p></div>
                <div><p class="text-gray-500">Payment Terms</p><p class="mt-0.5">{{ $supplier->payment_terms ?? '—' }}</p></div>
                <div><p class="text-gray-500">Status</p>
                    @if($supplier->is_active)<span class="badge badge-green">Active</span>
                    @else<span class="badge badge-gray">Inactive</span>@endif
                </div>
            </div>
        </div>
        <div class="col-span-2 card">
            <div class="card-header"><h3>Recent Purchase Orders</h3></div>
            <div class="table-wrapper">
                <table class="table">
                    <thead><tr><th>PO #</th><th>Date</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($supplier->purchaseOrders as $po)
                        <tr>
                            <td><a href="{{ route('inventory.purchase-orders.show', $po) }}" class="font-mono font-medium hover:underline">#{{ $po->id }}</a></td>
                            <td class="text-gray-500">{{ \Carbon\Carbon::parse($po->order_date)->format('M d, Y') }}</td>
                            <td>{{ $sym }}{{ number_format($po->total_amount/100,2) }}</td>
                            <td>
                                @if($po->status === 'pending')<span class="badge badge-yellow">Pending</span>
                                @elseif($po->status === 'received')<span class="badge badge-green">Received</span>
                                @else<span class="badge badge-gray">{{ ucfirst($po->status) }}</span>@endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-gray-400 py-6">No purchase orders.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
