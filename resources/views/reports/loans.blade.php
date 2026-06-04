@extends('layouts.app')
@section('title', 'Loans Report')
@section('breadcrumb')
    <span class="text-gray-500">Reports</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Loans Report</span>
@endsection
@section('content')
<div class="pt-6 space-y-5">
    <div class="flex items-center justify-between">
        <h1>Employee Loans Report</h1>
        <a href="{{ route('reports.export', 'loans') }}" class="btn-secondary">
            <i data-lucide="download" class="w-4 h-4"></i> Export CSV
        </a>
    </div>

    <div class="grid grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="stat-label">Issued (Period)</p>
            <p class="stat-value">{{ $currencySymbol }}{{ number_format($totals['issued'] / 100, 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Outstanding</p>
            <p class="stat-value {{ $totals['outstanding'] > 0 ? 'text-yellow-600' : '' }}">
                {{ $currencySymbol }}{{ number_format($totals['outstanding'] / 100, 2) }}
            </p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Fully Repaid</p>
            <p class="stat-value">{{ $currencySymbol }}{{ number_format($totals['repaid'] / 100, 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Written Off</p>
            <p class="stat-value {{ $totals['written_off'] > 0 ? 'text-red-600' : '' }}">
                {{ $currencySymbol }}{{ number_format($totals['written_off'] / 100, 2) }}
            </p>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Employee</th>
                    <th>Loan Amount</th>
                    <th>Remaining</th>
                    <th>Source</th>
                    <th>Status</th>
                    <th>Issued</th>
                    <th>Repayments</th>
                </tr></thead>
                <tbody>
                    @forelse($loans as $loan)
                    <tr>
                        <td class="font-medium">{{ $loan->employee?->name ?? '—' }}</td>
                        <td>{{ $currencySymbol }}{{ number_format($loan->amount / 100, 2) }}</td>
                        <td class="font-medium">{{ $currencySymbol }}{{ number_format($loan->remaining_balance / 100, 2) }}</td>
                        <td class="capitalize text-gray-500">{{ str_replace('_', ' ', $loan->source_type) }}</td>
                        <td>
                            @if($loan->status === 'outstanding') <span class="badge badge-yellow">Outstanding</span>
                            @elseif($loan->status === 'repaid') <span class="badge badge-green">Repaid</span>
                            @else <span class="badge badge-red">Written Off</span>
                            @endif
                        </td>
                        <td class="text-gray-500 text-xs">{{ $loan->created_at->format('M d, Y') }}</td>
                        <td class="text-gray-500 text-xs">
                            {{ $loan->repayments->count() }} payment(s) —
                            {{ $currencySymbol }}{{ number_format($loan->repayments->sum('amount') / 100, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-400 py-8">No loans found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($loans->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $loans->links() }}</div>
        @endif
    </div>
</div>
@endsection
