@extends('layouts.app')
@section('title', 'Profit & Loss Statement')
@section('breadcrumb')
    <span class="text-gray-500">Reports</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Profit & Loss</span>
@endsection
@section('content')
<div class="pt-6 space-y-5">
    <div class="flex items-center justify-between">
        <h1>Profit & Loss Statement</h1>
        <div class="flex gap-2">
            <a href="{{ route('reports.export', 'profit-loss') }}?date_from={{ $from->toDateString() }}&date_to={{ $to->toDateString() }}"
                class="btn-secondary"><i data-lucide="download" class="w-4 h-4"></i> Export CSV</a>
            <button onclick="window.print()" class="btn-secondary"><i data-lucide="printer" class="w-4 h-4"></i> Print</button>
        </div>
    </div>

    <form method="GET" class="card">
        <div class="card-body flex gap-3 items-end">
            <div><label class="form-label">From</label><input type="date" name="date_from" class="form-input" value="{{ $from->toDateString() }}"></div>
            <div><label class="form-label">To</label><input type="date" name="date_to" class="form-input" value="{{ $to->toDateString() }}"></div>
            <button type="submit" class="btn-primary">Apply</button>
        </div>
    </form>

    <div class="card max-w-2xl">
        <div class="card-header">
            <h3>{{ $from->format('M d, Y') }} — {{ $to->format('M d, Y') }}</h3>
        </div>
        <div class="card-body space-y-1 text-sm">

            {{-- Revenue --}}
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="font-medium text-gray-900">Total Sales Revenue</span>
                <span class="font-medium">{{ $currencySymbol }}{{ number_format($revenue/100,2) }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100 text-gray-600 pl-4">
                <span>Cost of Goods Sold (COGS)</span>
                <span>-{{ $currencySymbol }}{{ number_format($cogs/100,2) }}</span>
            </div>
            <div class="flex justify-between py-2.5 border-b-2 border-gray-300 font-semibold">
                <span>Gross Profit</span>
                <span class="{{ $grossProfit >= 0 ? 'text-green-700' : 'text-red-600' }}">
                    {{ $currencySymbol }}{{ number_format($grossProfit/100,2) }}
                </span>
            </div>

            {{-- Expenses breakdown --}}
            <div class="pt-2">
                <p class="text-xs text-gray-400 uppercase tracking-wider font-medium mb-1">Operating Expenses</p>
            </div>
            @foreach($expenses as $exp)
            <div class="flex justify-between py-1.5 text-gray-600 pl-4">
                <span class="capitalize">{{ $exp->category }}</span>
                <span>-{{ $currencySymbol }}{{ number_format($exp->total/100,2) }}</span>
            </div>
            @endforeach
            <div class="flex justify-between py-1.5 text-gray-600 pl-4 border-t border-gray-100">
                <span>Employee Salaries</span>
                <span>-{{ $currencySymbol }}{{ number_format($salaries/100,2) }}</span>
            </div>
            @if($donations > 0)
            <div class="flex justify-between py-1.5 text-gray-600 pl-4">
                <span>Donations Given</span>
                <span>-{{ $currencySymbol }}{{ number_format($donations/100,2) }}</span>
            </div>
            @endif
            @if($writtenOff > 0)
            <div class="flex justify-between py-1.5 text-gray-600 pl-4">
                <span>Loans Written Off</span>
                <span>-{{ $currencySymbol }}{{ number_format($writtenOff/100,2) }}</span>
            </div>
            @endif
            <div class="flex justify-between py-2 border-t border-gray-200 text-gray-700 font-medium">
                <span>Total Deductions</span>
                <span>-{{ $currencySymbol }}{{ number_format($totalDeductions/100,2) }}</span>
            </div>

            {{-- Net Profit --}}
            <div class="flex justify-between py-4 border-t-2 border-gray-900 text-lg font-bold">
                <span>Net Profit</span>
                <span class="{{ $netProfit >= 0 ? 'text-green-700' : 'text-red-600' }}">
                    {{ $currencySymbol }}{{ number_format($netProfit/100,2) }}
                    @if($netProfit < 0)
                        <span class="text-sm font-normal">(Net Loss)</span>
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
