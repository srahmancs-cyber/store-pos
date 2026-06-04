@extends('layouts.app')
@section('title', 'Tax Report')
@section('breadcrumb')
    <span class="text-gray-500">Reports</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Tax Report</span>
@endsection
@section('content')
<div class="pt-6 space-y-5">
    <div class="flex items-center justify-between">
        <h1>Tax Report</h1>
        <a href="{{ route('reports.export', 'tax') }}?date_from={{ $from->toDateString() }}&date_to={{ $to->toDateString() }}"
            class="btn-secondary"><i data-lucide="download" class="w-4 h-4"></i> Export CSV</a>
    </div>

    <form method="GET" class="card">
        <div class="card-body flex gap-3 items-end">
            <div><label class="form-label">From</label><input type="date" name="date_from" class="form-input" value="{{ $from->toDateString() }}"></div>
            <div><label class="form-label">To</label><input type="date" name="date_to" class="form-input" value="{{ $to->toDateString() }}"></div>
            <button type="submit" class="btn-primary">Apply</button>
        </div>
    </form>

    <div class="grid grid-cols-3 gap-4">
        <div class="stat-card">
            <p class="stat-label">Tax Name</p>
            <p class="stat-value text-xl">{{ $taxName }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Tax Rate</p>
            <p class="stat-value">{{ $taxRate }}%</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total Tax Collected</p>
            <p class="stat-value">{{ $currencySymbol }}{{ number_format($totalTaxCollected / 100, 2) }}</p>
        </div>
    </div>

    <div class="card max-w-xl">
        <div class="card-header"><h3>Summary</h3></div>
        <div class="card-body space-y-3 text-sm">
            <div class="flex justify-between text-gray-600 py-2 border-b border-gray-100">
                <span>Taxable Sales Revenue</span>
                <span class="font-medium">{{ $currencySymbol }}{{ number_format(($taxByRate->taxable_revenue ?? 0) / 100, 2) }}</span>
            </div>
            <div class="flex justify-between text-gray-600 py-2 border-b border-gray-100">
                <span>Number of Taxable Sales</span>
                <span class="font-medium">{{ $taxByRate->sale_count ?? 0 }}</span>
            </div>
            <div class="flex justify-between py-2 font-semibold text-base">
                <span>Total {{ $taxName }} Collected</span>
                <span class="text-green-700">{{ $currencySymbol }}{{ number_format($totalTaxCollected / 100, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
