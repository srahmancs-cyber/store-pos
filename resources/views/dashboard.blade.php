@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <span class="text-gray-900 font-medium">Dashboard</span>
@endsection

@section('content')
<div class="pt-6 space-y-6">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="stat-label">Today's Revenue</p>
            <p class="stat-value">{{ \App\Models\Setting::get('currency_symbol','$') }}{{ number_format($todayRevenue/100,2) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Today's Sales</p>
            <p class="stat-value">{{ $todayCount }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">This Month</p>
            <p class="stat-value">{{ \App\Models\Setting::get('currency_symbol','$') }}{{ number_format($monthRevenue/100,2) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Outstanding Loans</p>
            <p class="stat-value">{{ $outstandingLoans }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6">

        {{-- Low Stock Alerts --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-yellow-600"></i>
                    Low Stock
                    @if($lowStockProducts->count())
                        <span class="badge badge-yellow">{{ $lowStockProducts->count() }}</span>
                    @endif
                </h3>
                <a href="{{ route('inventory.products.index') }}?low_stock=1" class="btn btn-secondary btn-sm">View All</a>
            </div>
            @if($lowStockProducts->isEmpty())
                <div class="card-body text-sm text-gray-400">All products are adequately stocked.</div>
            @else
                <div class="table-wrapper">
                    <table class="table">
                        <thead><tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Reorder At</th>
                        </tr></thead>
                        <tbody>
                            @foreach($lowStockProducts as $p)
                            <tr>
                                <td class="font-medium">
                                    <a href="{{ route('inventory.products.show', $p) }}" class="hover:underline">{{ $p->name }}</a>
                                </td>
                                <td class="text-gray-500">{{ $p->category?->name ?? '—' }}</td>
                                <td><span class="badge badge-red">{{ $p->current_stock }}</span></td>
                                <td class="text-gray-500">{{ $p->reorder_point }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Overdue Bills --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
                    Overdue Bills
                    @if($overdueBills->count())
                        <span class="badge badge-red">{{ $overdueBills->count() }}</span>
                    @endif
                </h3>
                <a href="{{ route('finances.bills.index') }}" class="btn btn-secondary btn-sm">View All</a>
            </div>
            @if($overdueBills->isEmpty())
                <div class="card-body text-sm text-gray-400">No overdue bills.</div>
            @else
                <div class="table-wrapper">
                    <table class="table">
                        <thead><tr>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                        </tr></thead>
                        <tbody>
                            @foreach($overdueBills as $bill)
                            <tr>
                                <td class="font-medium">{{ $bill->description }}</td>
                                <td>{{ \App\Models\Setting::get('currency_symbol','$') }}{{ number_format($bill->amount/100,2) }}</td>
                                <td><span class="badge badge-red">{{ \Carbon\Carbon::parse($bill->due_date)->format('M d, Y') }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Sales --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h3>Recent Sales</h3>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">View All</a>
        </div>
        @if($recentSales->isEmpty())
            <div class="card-body text-sm text-gray-400">No sales recorded yet.</div>
        @else
            <div class="table-wrapper">
                <table class="table">
                    <thead><tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Cashier</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr></thead>
                    <tbody>
                        @foreach($recentSales as $sale)
                        <tr>
                            <td><a href="{{ route('sales.show', $sale) }}" class="font-medium hover:underline">#{{ $sale->id }}</a></td>
                            <td class="text-gray-500">{{ $sale->created_at->format('M d, H:i') }}</td>
                            <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td class="text-gray-500">{{ $sale->user?->name }}</td>
                            <td class="font-medium">{{ \App\Models\Setting::get('currency_symbol','$') }}{{ number_format($sale->final_amount/100,2) }}</td>
                            <td>
                                @if($sale->status === 'completed')
                                    <span class="badge badge-green">Completed</span>
                                @elseif($sale->status === 'voided')
                                    <span class="badge badge-red">Voided</span>
                                @else
                                    <span class="badge badge-gray">{{ ucfirst($sale->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
