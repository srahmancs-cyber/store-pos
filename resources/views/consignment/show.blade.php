@extends('layouts.app')
@section('title', $consignment->name)
@section('breadcrumb')
    <a href="{{ route('consignment.index') }}" class="text-gray-500 hover:text-gray-900">Consignment</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">{{ $consignment->name }}</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 space-y-6" x-data="{ showPayout: false }">

    <div class="flex items-center justify-between">
        <div>
            <h1>{{ $consignment->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $consignment->default_commission_rate }}% commission on {{ str_replace('_',' ',$consignment->commission_basis) }}
                · {{ str_replace('_',' ', $consignment->payout_frequency) }} payouts
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('consignment.edit', $consignment) }}" class="btn-secondary">Edit</a>
            <button @click="showPayout = true" class="btn-primary">
                <i data-lucide="banknote" class="w-4 h-4"></i> Generate Payout
            </button>
        </div>
    </div>

    {{-- Pending payout summary --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="stat-card">
            <p class="stat-label">Pending Items Sold</p>
            <p class="stat-value">{{ $pendingData['itemsSold'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Store Commission (pending)</p>
            <p class="stat-value">{{ $sym }}{{ number_format($pendingData['storeCommission']/100,2) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Vendor Payout Due</p>
            <p class="stat-value {{ $pendingData['vendorPayout'] > 0 ? 'text-yellow-600' : '' }}">
                {{ $sym }}{{ number_format($pendingData['vendorPayout']/100,2) }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-5">
        {{-- Products --}}
        <div class="col-span-1 card">
            <div class="card-header"><h3>Consignment Products</h3></div>
            <div class="table-wrapper">
                <table class="table">
                    <thead><tr><th>Product</th><th>Rate</th><th>Stock</th></tr></thead>
                    <tbody>
                        @forelse($consignment->products as $p)
                        <tr>
                            <td class="font-medium text-sm">{{ $p->name }}</td>
                            <td class="text-xs">{{ $p->consignment_rate ?? $consignment->default_commission_rate }}%</td>
                            <td>{{ $p->current_stock }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-gray-400 py-4">No products assigned.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Payout History --}}
        <div class="col-span-2 card">
            <div class="card-header"><h3>Payout History</h3></div>
            <div class="table-wrapper">
                <table class="table">
                    <thead><tr>
                        <th>Period</th>
                        <th>Items Sold</th>
                        <th>Sales Total</th>
                        <th>Store Commission</th>
                        <th>Vendor Payout</th>
                        <th>Status</th>
                        <th></th>
                    </tr></thead>
                    <tbody>
                        @forelse($consignment->payouts as $payout)
                        <tr>
                            <td class="text-xs text-gray-500">
                                {{ $payout->period_start->format('M d') }} – {{ $payout->period_end->format('M d, Y') }}
                            </td>
                            <td>{{ $payout->items_sold }}</td>
                            <td>{{ $sym }}{{ number_format($payout->total_sales_amount/100,2) }}</td>
                            <td>{{ $sym }}{{ number_format($payout->store_commission_amount/100,2) }}</td>
                            <td class="font-medium">{{ $sym }}{{ number_format($payout->vendor_payout_amount/100,2) }}</td>
                            <td>
                                @if($payout->status === 'paid') <span class="badge badge-green">Paid</span>
                                @else <span class="badge badge-yellow">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($payout->status === 'pending')
                                <form method="POST" action="{{ route('consignment.payout.paid', $payout) }}"
                                    x-data="{ method: 'bank', date: '{{ now()->toDateString() }}' }">
                                    @csrf
                                    <div class="flex gap-1 items-center">
                                        <select name="payment_method" x-model="method" class="form-select text-xs py-1 w-20">
                                            <option value="bank">Bank</option>
                                            <option value="cash">Cash</option>
                                        </select>
                                        <input type="hidden" name="paid_date" :value="date">
                                        <button class="btn btn-primary btn-sm">Pay</button>
                                    </div>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-gray-400 py-4">No payouts yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Generate Payout Modal --}}
    <div x-show="showPayout" x-cloak class="modal-backdrop" @click.self="showPayout = false">
        <div class="modal max-w-md">
            <div class="modal-header">
                <h3>Generate Payout Statement</h3>
                <button @click="showPayout = false"><i data-lucide="x" class="w-4 h-4 text-gray-400"></i></button>
            </div>
            <form method="POST" action="{{ route('consignment.payout.generate', $consignment) }}">
                @csrf
                <div class="modal-body space-y-4">
                    <div class="alert alert-info text-xs">
                        <i data-lucide="info" class="w-4 h-4 flex-shrink-0"></i>
                        This generates a statement for all unpaid consignment sales in the selected period.
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Period Start <span class="text-red-500">*</span></label>
                            <input type="date" name="period_start" class="form-input"
                                value="{{ now()->startOfMonth()->toDateString() }}" required>
                        </div>
                        <div>
                            <label class="form-label">Period End <span class="text-red-500">*</span></label>
                            <input type="date" name="period_end" class="form-input"
                                value="{{ now()->toDateString() }}" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-input" rows="2" placeholder="Optional notes for this payout"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="showPayout = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Generate Statement</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
