@extends('layouts.app')
@section('title', 'Owner Equity Report')
@section('breadcrumb')
    <span class="text-gray-500">Reports</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Owner Equity</span>
@endsection
@section('content')
<div class="pt-6 space-y-5">
    <div class="flex items-center justify-between">
        <h1>Owner Equity Report</h1>
        <button onclick="window.print()" class="btn-secondary">
            <i data-lucide="printer" class="w-4 h-4"></i> Print
        </button>
    </div>

    <div class="space-y-6">
        @foreach($owners as $row)
        @php $owner = $row['owner']; @endphp
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3>{{ $owner->name }}</h3>
                <span class="badge badge-gray">{{ $owner->profit_share_percentage }}% share</span>
            </div>
            <div class="card-body grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Total Invested</p>
                    <p class="text-lg font-semibold mt-0.5">{{ $currencySymbol }}{{ number_format($row['total_invested'] / 100, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Total Withdrawn</p>
                    <p class="text-lg font-semibold mt-0.5">{{ $currencySymbol }}{{ number_format($row['total_withdrawn'] / 100, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Profit Allocated</p>
                    <p class="text-lg font-semibold mt-0.5">{{ $currencySymbol }}{{ number_format($row['total_profit'] / 100, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Net Equity</p>
                    <p class="text-xl font-bold mt-0.5 {{ $row['equity'] >= 0 ? 'text-green-700' : 'text-red-600' }}">
                        {{ $currencySymbol }}{{ number_format($row['equity'] / 100, 2) }}
                    </p>
                </div>
            </div>

            {{-- Transaction history --}}
            <div class="border-t border-gray-100">
                <div class="table-wrapper">
                    <table class="table">
                        <thead><tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Notes</th>
                        </tr></thead>
                        <tbody>
                            @forelse($row['owner']->transactions->sortByDesc('transaction_date') as $tx)
                            <tr>
                                <td class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($tx->transaction_date)->format('M d, Y') }}</td>
                                <td>
                                    @if($tx->type === 'investment') <span class="badge badge-green">Investment</span>
                                    @elseif($tx->type === 'withdrawal') <span class="badge badge-red">Withdrawal</span>
                                    @else <span class="badge badge-blue">Profit</span>
                                    @endif
                                </td>
                                <td class="font-medium text-sm">
                                    {{ $tx->type === 'withdrawal' ? '-' : '+' }}{{ $currencySymbol }}{{ number_format($tx->amount / 100, 2) }}
                                </td>
                                <td class="text-gray-400 text-xs truncate">{{ $tx->notes ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-gray-400 py-4">No transactions.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
