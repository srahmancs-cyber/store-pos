@extends('layouts.app')
@section('title', 'Capital Injections')
@section('breadcrumb')
    <span class="text-gray-500">Finances</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Capital Injections</span>
@endsection

@section('content')
@php
    $sym    = \App\Models\Setting::get('currency_symbol', '$');
    $owners = \App\Models\Owner::orderBy('sort_order')->get();
@endphp
<div class="pt-6 space-y-4" x-data="{ showCreate: false, sourceType: 'owner' }">

    <div class="flex items-center justify-between">
        <h1>Capital Injections</h1>
        <button @click="showCreate = true" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i> New Injection
        </button>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Source</th>
                    <th>Destination</th>
                    <th>Purpose</th>
                    <th>By</th>
                </tr></thead>
                <tbody>
                    @forelse($injections as $injection)
                    <tr>
                        <td class="text-gray-500">
                            {{ \Carbon\Carbon::parse($injection->transaction_date)->format('M d, Y') }}
                        </td>
                        <td class="font-medium">{{ $sym }}{{ number_format($injection->amount / 100, 2) }}</td>
                        <td>
                            @if($injection->source_type === 'owner' && $injection->ownerTransaction)
                                <span class="font-medium">{{ $injection->ownerTransaction->owner?->name }}</span>
                                <span class="badge badge-blue ml-1">Owner</span>
                            @else
                                <span class="text-gray-500">External</span>
                            @endif
                        </td>
                        <td class="capitalize">
                            @if($injection->destination_type === 'cash')
                                <span class="badge badge-gray">Cash Drawer</span>
                            @else
                                <span class="badge badge-gray">Bank</span>
                            @endif
                        </td>
                        <td class="text-gray-500 max-w-xs truncate">{{ $injection->purpose ?? '—' }}</td>
                        <td class="text-gray-500">{{ $injection->creator?->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-gray-400 py-8">No capital injections recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($injections->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $injections->links() }}</div>
        @endif
    </div>

    {{-- Create Modal --}}
    <div x-show="showCreate" x-cloak class="modal-backdrop" @click.self="showCreate = false">
        <div class="modal">
            <div class="modal-header">
                <h3>New Capital Injection</h3>
                <button @click="showCreate = false">
                    <i data-lucide="x" class="w-4 h-4 text-gray-400"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('finances.capital-injections.store') }}">
                @csrf
                <div class="modal-body space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Amount <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" step="0.01" min="0.01"
                                class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="transaction_date" class="form-input" required
                                value="{{ now()->toDateString() }}">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Source <span class="text-red-500">*</span></label>
                            <select name="source_type" x-model="sourceType" class="form-select" required>
                                <option value="owner">Owner</option>
                                <option value="external">External</option>
                            </select>
                        </div>
                        <div x-show="sourceType === 'owner'">
                            <label class="form-label">Which Owner <span class="text-red-500">*</span></label>
                            <select name="source_id" class="form-select">
                                @foreach(\App\Models\Owner::where('is_active', true)->orderBy('sort_order')->get() as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Destination <span class="text-red-500">*</span></label>
                        <select name="destination_type" class="form-select" required>
                            <option value="bank">Bank Account</option>
                            <option value="cash">Cash Drawer</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Purpose</label>
                        <textarea name="purpose" class="form-input" rows="2"
                            placeholder="Reason for this injection (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="showCreate = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Record Injection</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
