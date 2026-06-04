@extends('layouts.app')
@section('title', 'Owner Dashboard')
@section('breadcrumb')
    <span class="text-gray-900 font-medium">Owner Dashboard</span>
@endsection

@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol', '$'); @endphp
<div class="pt-6 space-y-6"
    x-data="{ showInvest: false, showWithdraw: false }">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1>Owner Dashboard</h1>
        <div class="flex gap-2">
            <button @click="showInvest = true" class="btn-primary">
                <i data-lucide="arrow-down-circle" class="w-4 h-4"></i> Record Investment
            </button>
            <button @click="showWithdraw = true" class="btn-secondary">
                <i data-lucide="arrow-up-circle" class="w-4 h-4"></i> Record Withdrawal
            </button>
        </div>
    </div>

    {{-- Store Balances --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="stat-card flex items-center gap-4">
            <i data-lucide="landmark" class="w-6 h-6 text-gray-400"></i>
            <div>
                <p class="stat-label">Bank Balance</p>
                <p class="stat-value">{{ $sym }}{{ number_format($bankBalance / 100, 2) }}</p>
            </div>
        </div>
        <div class="stat-card flex items-center gap-4">
            <i data-lucide="wallet" class="w-6 h-6 text-gray-400"></i>
            <div>
                <p class="stat-label">Cash Drawer</p>
                <p class="stat-value">{{ $sym }}{{ number_format($cashBalance / 100, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Owner Equity Cards --}}
    @forelse($owners as $ownerData)
    @php $owner = $ownerData['owner']; @endphp
    <div class="card {{ !$owner->is_active ? 'opacity-60' : '' }}">
        <div class="card-header flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h3>{{ $owner->name }}</h3>
                @if(!$owner->is_active)
                    <span class="badge badge-gray">Inactive</span>
                @endif
            </div>
            <span class="badge badge-gray">{{ $owner->profit_share_percentage }}% profit share</span>
        </div>

        <div class="card-body">
            <div class="grid grid-cols-4 gap-4 mb-5">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Total Invested</p>
                    <p class="text-lg font-bold mt-1">{{ $sym }}{{ number_format($ownerData['total_invested'] / 100, 2) }}</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Total Withdrawn</p>
                    <p class="text-lg font-bold mt-1">{{ $sym }}{{ number_format($ownerData['total_withdrawn'] / 100, 2) }}</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Profit Allocated</p>
                    <p class="text-lg font-bold mt-1">{{ $sym }}{{ number_format($ownerData['total_profit'] / 100, 2) }}</p>
                </div>
                <div class="text-center p-4 rounded-lg {{ $ownerData['equity'] >= 0 ? 'bg-green-50' : 'bg-red-50' }}">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Current Equity</p>
                    <p class="text-xl font-bold mt-1 {{ $ownerData['equity'] >= 0 ? 'text-green-700' : 'text-red-600' }}">
                        {{ $sym }}{{ number_format($ownerData['equity'] / 100, 2) }}
                    </p>
                </div>
            </div>

            {{-- Transaction History --}}
            <div class="border border-gray-100 rounded-lg overflow-hidden">
                <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Recent Transactions</p>
                </div>
                <table class="table">
                    <thead><tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Notes</th>
                    </tr></thead>
                    <tbody>
                        @forelse($ownerData['owner']->transactions->take(10) as $tx)
                        <tr>
                            <td class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($tx->transaction_date)->format('M d, Y') }}</td>
                            <td>
                                @if($tx->type === 'investment') <span class="badge badge-green">Investment</span>
                                @elseif($tx->type === 'withdrawal') <span class="badge badge-red">Withdrawal</span>
                                @else <span class="badge badge-blue">Profit Allocation</span>
                                @endif
                            </td>
                            <td class="font-medium text-sm">
                                {{ $tx->type === 'withdrawal' ? '-' : '+' }}{{ $sym }}{{ number_format($tx->amount / 100, 2) }}
                            </td>
                            <td class="text-gray-400 text-xs max-w-xs truncate">{{ $tx->notes ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-gray-400 py-4">No transactions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @empty
    <div class="card">
        <div class="card-body text-center py-12">
            <i data-lucide="briefcase" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
            <p class="text-gray-500">No owners added yet.</p>
            <a href="{{ route('owners.list') }}" class="btn-primary mt-4 inline-flex">
                <i data-lucide="users" class="w-4 h-4"></i> Manage Owners
            </a>
        </div>
    </div>
    @endforelse

    {{-- Investment Modal --}}
    <div x-show="showInvest" x-cloak class="modal-backdrop" @click.self="showInvest = false">
        <div class="modal max-w-md">
            <div class="modal-header">
                <h3>Record Investment</h3>
                <button @click="showInvest = false"><i data-lucide="x" class="w-4 h-4 text-gray-400"></i></button>
            </div>
            <form method="POST" action="{{ route('owners.investments.store') }}">
                @csrf
                <div class="modal-body space-y-4">
                    <div>
                        <label class="form-label">Owner <span class="text-red-500">*</span></label>
                        <select name="owner_id" class="form-select" required>
                            <option value="">Select owner</option>
                            @foreach($owners as $od)
                            @if($od['owner']->is_active)
                            <option value="{{ $od['owner']->id }}">{{ $od['owner']->name }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Amount <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" step="0.01" min="0.01" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="transaction_date" class="form-input" value="{{ now()->toDateString() }}" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Add to</label>
                        <select name="destination_type" class="form-select">
                            <option value="bank">Bank Account</option>
                            <option value="cash">Cash Drawer</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-input" placeholder="Optional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="showInvest = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Record Investment</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Withdrawal Modal --}}
    <div x-show="showWithdraw" x-cloak class="modal-backdrop" @click.self="showWithdraw = false">
        <div class="modal max-w-md">
            <div class="modal-header">
                <h3>Record Withdrawal</h3>
                <button @click="showWithdraw = false"><i data-lucide="x" class="w-4 h-4 text-gray-400"></i></button>
            </div>
            <form method="POST" action="{{ route('owners.withdrawals.store') }}">
                @csrf
                <div class="modal-body space-y-4">
                    <div>
                        <label class="form-label">Owner <span class="text-red-500">*</span></label>
                        <select name="owner_id" class="form-select" required>
                            <option value="">Select owner</option>
                            @foreach($owners as $od)
                            @if($od['owner']->is_active)
                            <option value="{{ $od['owner']->id }}">{{ $od['owner']->name }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Amount <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" step="0.01" min="0.01" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="transaction_date" class="form-input" value="{{ now()->toDateString() }}" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Take from</label>
                        <select name="source_type" class="form-select">
                            <option value="bank">Bank Account</option>
                            <option value="cash">Cash Drawer</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-input" placeholder="Optional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="showWithdraw = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Record Withdrawal</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
