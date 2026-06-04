@extends('layouts.app')
@section('title', 'Employee Performance')
@section('breadcrumb')
    <span class="text-gray-500">Reports</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Employee Performance</span>
@endsection
@section('content')
<div class="pt-6 space-y-5">
    <div class="flex items-center justify-between">
        <h1>Employee Performance</h1>
        <a href="{{ route('reports.export', 'employees') }}?date_from={{ $from->toDateString() }}&date_to={{ $to->toDateString() }}"
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
                    <th>Employee</th>
                    <th>Role</th>
                    <th>Sales Count</th>
                    <th>Sales Revenue</th>
                    <th>Avg. Sale</th>
                    <th>Hours Worked</th>
                </tr></thead>
                <tbody>
                    @forelse($employeeData as $row)
                    <tr>
                        <td class="font-medium">{{ $row['employee']->name }}</td>
                        <td class="capitalize text-gray-500">{{ $row['employee']->role }}</td>
                        <td>{{ $row['sales_count'] }}</td>
                        <td>{{ $currencySymbol }}{{ number_format($row['sales_revenue'] / 100, 2) }}</td>
                        <td class="text-gray-500">
                            {{ $row['sales_count'] > 0
                                ? $currencySymbol . number_format($row['sales_revenue'] / $row['sales_count'] / 100, 2)
                                : '—' }}
                        </td>
                        <td>{{ $row['hours_worked'] > 0 ? number_format($row['hours_worked'], 1) . 'h' : '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-gray-400 py-8">No employee data for this period.</td></tr>
                    @endforelse
                </tbody>
                @if(count($employeeData) > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-200 font-bold">
                        <td class="px-4 py-3" colspan="2">Total</td>
                        <td class="px-4 py-3">{{ collect($employeeData)->sum('sales_count') }}</td>
                        <td class="px-4 py-3">{{ $currencySymbol }}{{ number_format(collect($employeeData)->sum('sales_revenue') / 100, 2) }}</td>
                        <td class="px-4 py-3">—</td>
                        <td class="px-4 py-3">{{ number_format(collect($employeeData)->sum('hours_worked'), 1) }}h</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
