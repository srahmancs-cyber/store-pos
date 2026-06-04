@extends('layouts.app')
@section('title', 'Bills')
@section('breadcrumb')
    <span class="text-gray-500">Finances</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Bills</span>
@endsection

@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol', '$'); @endphp
<div class="pt-6 space-y-4" x-data="{ showCreate: false }">

    <div class="flex items-center justify-between">
        <h1>Bills</h1>
        <button @click="showCreate = true" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Bill
        </button>
    </div>

    {{-- Summary stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="stat-card">
            <p class="stat-label">Total Unpaid</p>
            <p class="stat-value">{{ $sym }}{{ number_format($totalUnpaid / 100, 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Overdue Bills</p>
            <p class="stat-value {{ $overdueCount > 0 ? 'text-red-600' : '' }}">{{ $overdueCount }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total Bills</p>
            <p class="stat-value">{{ $bills->total() }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card">
        <div class="card-body flex flex-wrap gap-3 items-end">
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select w-36">
                    <option value="">All</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div>
                <label class="form-label">Due From</label>
                <input type="date" name="date_from" class="form-input" value="{{ request('date_from') }}">
            </div>
            <div>
                <label class="form-label">Due To</label>
                <input type="date" name="date_to" class="form-input" value="{{ request('date_to') }}">
            </div>
            <button type="submit" class="btn-secondary">Filter</button>
            <a href="{{ route('finances.bills.index') }}" class="btn-secondary">Clear</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Paid Date</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($bills as $bill)
                    @php
                        $isOverdue = $bill->status === 'unpaid' && \Carbon\Carbon::parse($bill->due_date)->isPast();
                    @endphp
                    <tr class="{{ $isOverdue ? 'bg-red-50' : '' }}">
                        <td class="font-medium">{{ $bill->description }}</td>
                        <td>{{ $sym }}{{ number_format($bill->amount / 100, 2) }}</td>
                        <td>
                            <span class="{{ $isOverdue ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                {{ \Carbon\Carbon::parse($bill->due_date)->format('M d, Y') }}
                                @if($isOverdue)
                                    <span class="badge badge-red ml-1">Overdue</span>
                                @endif
                            </span>
                        </td>
                        <td>
                            @if($bill->status === 'paid')
                                <span class="badge badge-green">Paid</span>
                            @else
                                <span class="badge badge-yellow">Unpaid</span>
                            @endif
                        </td>
                        <td class="text-gray-500">
                            {{ $bill->paid_date ? \Carbon\Carbon::parse($bill->paid_date)->format('M d, Y') : '—' }}
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1">
                                @if($bill->status === 'unpaid')
                                <form method="POST" action="{{ route('finances.bills.pay', $bill) }}" class="flex items-center gap-1">
                                    @csrf
                                    <select name="payment_method" class="form-select text-xs py-1 w-20">
                                        <option value="bank">Bank</option>
                                        <option value="cash">Cash</option>
                                    </select>
                                    <button class="btn btn-primary btn-sm">Mark Paid</button>
                                </form>
                                <a href="{{ route('finances.bills.edit', $bill) }}" class="btn btn-secondary btn-sm">Edit</a>
                                @endif
                                <form method="POST" action="{{ route('finances.bills.destroy', $bill) }}"
                                    onsubmit="return confirm('Delete this bill?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-gray-400 py-8">No bills found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bills->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $bills->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- Create Modal --}}
    <div x-show="showCreate" x-cloak class="modal-backdrop" @click.self="showCreate = false">
        <div class="modal">
            <div class="modal-header">
                <h3>Add Bill</h3>
                <button @click="showCreate = false">
                    <i data-lucide="x" class="w-4 h-4 text-gray-400"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('finances.bills.store') }}">
                @csrf
                <div class="modal-body space-y-4">
                    <div>
                        <label class="form-label">Description <span class="text-red-500">*</span></label>
                        <input type="text" name="description" class="form-input" required
                            value="{{ old('description') }}" placeholder="e.g. Monthly rent">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Amount <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" step="0.01" min="0.01"
                                class="form-input" required value="{{ old('amount') }}">
                        </div>
                        <div>
                            <label class="form-label">Due Date <span class="text-red-500">*</span></label>
                            <input type="date" name="due_date" class="form-input" required
                                value="{{ old('due_date') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="showCreate = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Create Bill</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
