@extends('layouts.app')
@section('title', 'Category Profit Report')
@section('breadcrumb')
    <span class="text-gray-500">Reports</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Category Profit</span>
@endsection
@section('content')
<div class="pt-6 space-y-5">
    <div class="flex items-center justify-between">
        <h1>Category Profit Report</h1>
        <a href="{{ route('reports.export', 'category-profit') }}?date_from={{ $from->toDateString() }}&date_to={{ $to->toDateString() }}"
            class="btn-secondary"><i data-lucide="download" class="w-4 h-4"></i> Export CSV</a>
    </div>

    <form method="GET" class="card">
        <div class="card-body flex gap-3 items-end">
            <div><label class="form-label">From</label><input type="date" name="date_from" class="form-input" value="{{ $from->toDateString() }}"></div>
            <div><label class="form-label">To</label><input type="date" name="date_to" class="form-input" value="{{ $to->toDateString() }}"></div>
            <button type="submit" class="btn-primary">Apply</button>
        </div>
    </form>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Category</th>
                    <th>Revenue</th>
                    <th>COGS</th>
                    <th>Profit</th>
                    <th>Margin %</th>
                </tr></thead>
                <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td class="font-medium">{{ $row->category_name }}</td>
                        <td>{{ $currencySymbol }}{{ number_format($row->revenue/100,2) }}</td>
                        <td>{{ $currencySymbol }}{{ number_format($row->cogs/100,2) }}</td>
                        <td class="font-medium {{ $row->profit >= 0 ? 'text-green-700' : 'text-red-600' }}">
                            {{ $currencySymbol }}{{ number_format($row->profit/100,2) }}
                        </td>
                        <td>
                            @if($row->revenue > 0)
                                <span class="{{ $row->margin >= 40 ? 'text-green-700' : ($row->margin >= 20 ? 'text-yellow-700' : 'text-red-600') }} font-medium">
                                    {{ number_format($row->margin, 2) }}%
                                </span>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-gray-400 py-8">No sales data for this period.</td></tr>
                    @endforelse
                </tbody>
                @if($data->count() > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-200 font-bold">
                        <td class="px-4 py-3">Total</td>
                        <td class="px-4 py-3">{{ $currencySymbol }}{{ number_format($data->sum('revenue')/100,2) }}</td>
                        <td class="px-4 py-3">{{ $currencySymbol }}{{ number_format($data->sum('cogs')/100,2) }}</td>
                        <td class="px-4 py-3">{{ $currencySymbol }}{{ number_format($data->sum('profit')/100,2) }}</td>
                        <td class="px-4 py-3">
                            @php $totalRev = $data->sum('revenue'); @endphp
                            {{ $totalRev > 0 ? number_format($data->sum('profit')/$totalRev*100,2) : '0.00' }}%
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
