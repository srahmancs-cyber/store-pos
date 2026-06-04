@extends('layouts.app')
@section('title', 'Expenses Report')
@section('breadcrumb')
    <span class="text-gray-500">Reports</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Expenses</span>
@endsection
@section('content')
<div class="pt-6 space-y-5">
    <div class="flex items-center justify-between">
        <h1>Expenses Report</h1>
        <a href="{{ route('reports.export', 'expenses') }}?date_from={{ $from->toDateString() }}&date_to={{ $to->toDateString() }}"
            class="btn-secondary"><i data-lucide="download" class="w-4 h-4"></i> Export CSV</a>
    </div>

    <form method="GET" class="card">
        <div class="card-body flex gap-3 items-end">
            <div><label class="form-label">From</label><input type="date" name="date_from" class="form-input" value="{{ $from->toDateString() }}"></div>
            <div><label class="form-label">To</label><input type="date" name="date_to" class="form-input" value="{{ $to->toDateString() }}"></div>
            <button type="submit" class="btn-primary">Apply</button>
        </div>
    </form>

    <div class="grid grid-cols-2 gap-5">
        {{-- By category --}}
        <div class="card">
            <div class="card-header"><h3>By Category</h3></div>
            <div class="table-wrapper">
                <table class="table">
                    <thead><tr><th>Category</th><th>Count</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse($byCategory as $cat => $data)
                        <tr>
                            <td class="capitalize font-medium">{{ $cat }}</td>
                            <td class="text-gray-500">{{ $data['count'] }}</td>
                            <td class="font-medium">{{ \App\Models\Setting::get('currency_symbol','$') }}{{ number_format($data['amount']/100,2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-gray-400 py-4">No data.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-200 font-bold">
                            <td class="px-4 py-3">Total</td>
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3">{{ \App\Models\Setting::get('currency_symbol','$') }}{{ number_format($total/100,2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Individual entries --}}
        <div class="card">
            <div class="card-header"><h3>All Expenses</h3></div>
            <div class="table-wrapper" style="max-height:400px; overflow-y:auto">
                <table class="table">
                    <thead class="sticky top-0 bg-white"><tr><th>Date</th><th>Category</th><th>Amount</th></tr></thead>
                    <tbody>
                        @forelse($expenses as $e)
                        <tr>
                            <td class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($e->date)->format('M d') }}</td>
                            <td class="capitalize">{{ $e->category }}</td>
                            <td>{{ \App\Models\Setting::get('currency_symbol','$') }}{{ number_format($e->amount/100,2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-gray-400 py-4">No expenses.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
