@extends('layouts.app')
@section('title', 'Sales Report')
@section('breadcrumb')
    <span class="text-gray-500">Reports</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Sales Report</span>
@endsection
@section('content')
<div class="pt-6 space-y-5">
    <div class="flex items-center justify-between">
        <h1>Sales Report</h1>
        <a href="{{ route('reports.export', 'sales') }}?date_from={{ $from->toDateString() }}&date_to={{ $to->toDateString() }}"
            class="btn-secondary">
            <i data-lucide="download" class="w-4 h-4"></i> Export CSV
        </a>
    </div>

    <form method="GET" class="card">
        <div class="card-body flex gap-3 items-end">
            <div><label class="form-label">From</label><input type="date" name="date_from" class="form-input" value="{{ $from->toDateString() }}"></div>
            <div><label class="form-label">To</label><input type="date" name="date_to" class="form-input" value="{{ $to->toDateString() }}"></div>
            <button type="submit" class="btn-primary">Apply</button>
        </div>
    </form>

    <div class="grid grid-cols-3 gap-4">
        <div class="stat-card"><p class="stat-label">Total Revenue</p><p class="stat-value">{{ $currencySymbol }}{{ number_format($totalRevenue/100,2) }}</p></div>
        <div class="stat-card"><p class="stat-label">Total Sales</p><p class="stat-value">{{ $totalCount }}</p></div>
        <div class="stat-card"><p class="stat-label">Avg. Order Value</p><p class="stat-value">{{ $currencySymbol }}{{ $totalCount > 0 ? number_format($totalRevenue/$totalCount/100,2) : '0.00' }}</p></div>
    </div>

    <div class="grid grid-cols-2 gap-5">
        <div class="card">
            <div class="card-header"><h3>Daily Summary</h3></div>
            <div class="table-wrapper">
                <table class="table">
                    <thead><tr><th>Date</th><th>Sales</th><th>Revenue</th></tr></thead>
                    <tbody>
                        @forelse($dailyTotals as $day)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($day->sale_date)->format('M d, Y') }}</td>
                            <td>{{ $day->count }}</td>
                            <td class="font-medium">{{ $currencySymbol }}{{ number_format($day->revenue/100,2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-gray-400 py-6">No sales in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="space-y-5">
            <div class="card">
                <div class="card-header"><h3>Payment Methods</h3></div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead><tr><th>Method</th><th>Count</th><th>Total</th></tr></thead>
                        <tbody>
                            @foreach($paymentBreakdown as $pm)
                            <tr>
                                <td class="capitalize font-medium">{{ str_replace('_',' ',$pm->payment_method) }}</td>
                                <td>{{ $pm->count }}</td>
                                <td>{{ $currencySymbol }}{{ number_format($pm->total/100,2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3>Top Products</h3></div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead><tr><th>Product</th><th>Qty</th><th>Revenue</th></tr></thead>
                        <tbody>
                            @foreach($topProducts as $p)
                            <tr>
                                <td class="font-medium">{{ $p->product_name }}</td>
                                <td>{{ $p->total_qty }}</td>
                                <td>{{ $currencySymbol }}{{ number_format($p->revenue/100,2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
