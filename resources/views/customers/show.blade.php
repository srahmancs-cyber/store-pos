@extends('layouts.app')
@section('title', $customer->name ?? 'Customer')
@section('breadcrumb')
    <a href="{{ route('customers.index') }}" class="text-gray-500 hover:text-gray-900">Customers</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">{{ $customer->name ?? 'Walk-in' }}</span>
@endsection
@section('content')
<div class="pt-6 space-y-5">
    <div class="flex items-center justify-between">
        <h1>{{ $customer->name ?? 'Customer #'.$customer->id }}</h1>
        <a href="{{ route('customers.edit', $customer) }}" class="btn-secondary">Edit</a>
    </div>
    <div class="grid grid-cols-3 gap-5">
        <div class="card">
            <div class="card-header"><h3>Details</h3></div>
            <div class="card-body space-y-3 text-sm">
                <div><p class="text-gray-500">Phone</p><p class="font-medium mt-0.5">{{ $customer->phone ?? '—' }}</p></div>
                <div><p class="text-gray-500">Email</p><p class="mt-0.5">{{ $customer->email ?? '—' }}</p></div>
                <div><p class="text-gray-500">Total Sales</p><p class="font-medium mt-0.5">{{ $customer->sales->count() }}</p></div>
                <div><p class="text-gray-500">Total Spent</p><p class="text-lg font-bold mt-0.5">{{ $sym }}{{ number_format($totalSpent/100,2) }}</p></div>
                <div><p class="text-gray-500">Customer Since</p><p class="mt-0.5">{{ $customer->created_at->format('M d, Y') }}</p></div>
            </div>
        </div>
        <div class="col-span-2 card">
            <div class="card-header"><h3>Purchase History</h3></div>
            <div class="table-wrapper">
                <table class="table">
                    <thead><tr><th>#</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($customer->sales as $sale)
                        <tr>
                            <td class="font-mono">#{{ $sale->id }}</td>
                            <td class="text-gray-500">{{ $sale->created_at->format('M d, Y') }}</td>
                            <td class="font-medium">{{ $sym }}{{ number_format($sale->final_amount/100,2) }}</td>
                            <td>
                                @if($sale->status==='completed')<span class="badge badge-green">Completed</span>
                                @else<span class="badge badge-red">{{ ucfirst($sale->status) }}</span>@endif
                            </td>
                            <td><a href="{{ route('sales.show',$sale) }}" class="btn btn-secondary btn-sm">View</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-gray-400 py-6">No purchases yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
