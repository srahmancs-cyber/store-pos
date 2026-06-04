@extends('layouts.app')
@section('title', 'Profit & Loss')
@section('breadcrumb')
    <span class="text-gray-500">Finances</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Profit & Loss</span>
@endsection

@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol', '$'); @endphp
<div class="pt-6 space-y-6">

    <div class="flex items-center justify-between">
        <h1>Profit & Loss</h1>
    </div>

    {{-- Calculate Form --}}
    <div class="card">
        <div class="card-header"><h3>Calculate Net Profit</h3></div>
        <form method="POST" action="{{ route('finances.profit.calculate') }}">
            @csrf
            <div class="card-body flex flex-wrap gap-4 items-end">
                <div>
                    <label class="form-label">Period Start <span class="text-red-500">*</span></label>
                    <input type="date" name="period_start" class="form-input"
                        value="{{ request('period_start', now()->startOfMonth()->toDateString()) }}" required>
                </div>
                <div>
                    <label class="form-label">Period End <span class="text-red-500">*</span></label>
                    <input type="date" name="period_end" class="form-input"
                        value="{{ request('period_end', now()->endOfMonth()->toDateString()) }}" required>
                </div>
                <div>
                    <label class="form-label">Other Income ({{ $sym }})</label>
                    <input type="number" name="other_income" step="0.01" min="0"
                        class="form-input w-36" value="0" placeholder="0.00">
                </div>
                <button type="submit" class="btn-primary">
                    <i data-lucide="calculator" class="w-4 h-4"></i>
                    Calculate
                </button>
            </div>
            <p class="px-6 pb-4 text-xs text-gray-400">
                If a calculation already exists for this period, you will be asked to confirm before overwriting.
            </p>
        </form>
    </div>

    {{-- Past Calculations --}}
    <div class="card">
        <div class="card-header"><h3>Calculation History</h3></div>
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Period</th>
                    <th>Revenue</th>
                    <th>COGS</th>
                    <th>Expenses</th>
                    <th>Salaries</th>
                    <th>Net Profit</th>
                    <th>Calculated</th>
                </tr></thead>
                <tbody>
                    @forelse($calculations as $calc)
                    <tr>
                        <td class="font-medium">
                            {{ \Carbon\Carbon::parse($calc->period_start)->format('M d') }}
                            – {{ \Carbon\Carbon::parse($calc->period_end)->format('M d, Y') }}
                        </td>
                        <td>{{ $sym }}{{ number_format($calc->total_sales_revenue / 100, 2) }}</td>
                        <td>{{ $sym }}{{ number_format($calc->cogs / 100, 2) }}</td>
                        <td>{{ $sym }}{{ number_format($calc->total_expenses / 100, 2) }}</td>
                        <td>{{ $sym }}{{ number_format($calc->total_salaries / 100, 2) }}</td>
                        <td>
                            <span class="font-bold {{ $calc->net_profit >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                {{ $sym }}{{ number_format($calc->net_profit / 100, 2) }}
                            </span>
                        </td>
                        <td class="text-gray-500 text-xs">{{ $calc->finalised_at?->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-gray-400 py-8">
                            No calculations yet. Use the form above to calculate profit for a period.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($calculations->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $calculations->links() }}</div>
        @endif
    </div>

</div>
@endsection
