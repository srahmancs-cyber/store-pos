@extends('layouts.app')
@section('title','Sales History')
@section('breadcrumb')
    <span class="text-gray-500">Sales</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Sales History</span>
@endsection
@section('content')
<div class="pt-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1>Sales History</h1>
        <a href="{{ route('sales.create') }}" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i> New Sale
        </a>
    </div>

    <form method="GET" class="card">
        <div class="card-body flex flex-wrap gap-3 items-end">
            <div>
                <label class="form-label">From</label>
                <input type="date" name="date_from" class="form-input" value="{{ request('date_from') }}">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" name="date_to" class="form-input" value="{{ request('date_to') }}">
            </div>
            <div class="w-36">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Voided</option>
                </select>
            </div>
            <div class="flex-1 min-w-40">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-input" placeholder="Sale # or customer" value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn-secondary">Filter</button>
            <a href="{{ route('sales.index') }}" class="btn-secondary">Clear</a>
            <a href="{{ route('reports.export', 'sales') }}?{{ http_build_query(request()->query()) }}" class="btn-secondary">
                <i data-lucide="download" class="w-4 h-4"></i> Export CSV
            </a>
        </div>
    </form>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Cashier</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td class="font-mono font-medium">#{{ $sale->id }}</td>
                        <td class="text-gray-500">{{ $sale->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                        <td class="text-gray-500">{{ $sale->user?->name }}</td>
                        <td class="font-medium">{{ $currencySymbol }}{{ number_format($sale->final_amount/100,2) }}</td>
                        <td>
                            @if($sale->status === 'completed') <span class="badge badge-green">Completed</span>
                            @elseif($sale->status === 'voided') <span class="badge badge-red">Voided</span>
                            @else <span class="badge badge-gray">{{ ucfirst($sale->status) }}</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary btn-sm">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-400 py-8">No sales found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $sales->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
