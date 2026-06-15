@extends('layouts.app')
@section('title', 'Add Owner / Investor')
@section('breadcrumb')
    <a href="{{ route('owners.list') }}" class="text-gray-500 hover:text-gray-900">Owner List</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Add</span>
@endsection
@section('content')
@php
    $currentTotal = \App\Models\Owner::where('is_active', true)->sum('profit_share_percentage');
    $remaining    = max(0, 100 - $currentTotal);
    $sym          = \App\Models\Setting::get('currency_symbol','$');
@endphp
<div class="pt-6 max-w-xl" x-data="{ type: '{{ old('type','owner') }}' }">
    <h1 class="mb-6">Add Owner / Investor</h1>

    @if($currentTotal > 0)
    <div class="alert alert-info mb-5">
        <i data-lucide="info" class="w-4 h-4 flex-shrink-0"></i>
        Current active shares: {{ number_format($currentTotal,2) }}%. Remaining: <strong>{{ number_format($remaining,2) }}%</strong>
    </div>
    @endif

    <form method="POST" action="{{ route('owners.store') }}" class="space-y-5">
        @csrf
        <div class="card">
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Type <span class="text-red-500">*</span></label>
                    <div class="flex gap-4 mt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="type" value="owner" x-model="type" class="text-gray-900"> Owner
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="type" value="investor" x-model="type" class="text-gray-900"> Investor
                        </label>
                    </div>
                </div>
                <div>
                    <label class="form-label">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Profit Share % <span class="text-red-500">*</span></label>
                        <input type="number" name="profit_share_percentage" step="0.01" min="0" max="100"
                            class="form-input" value="{{ old('profit_share_percentage', number_format($remaining,2,'.','')) }}" required>
                        @error('profit_share_percentage')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Profit Basis</label>
                        <select name="profit_basis" class="form-select">
                            <option value="net_profit" {{ old('profit_basis','net_profit')==='net_profit'?'selected':'' }}>% of Net Profit</option>
                            <option value="sales_revenue" {{ old('profit_basis')==='sales_revenue'?'selected':'' }}>% of Sales Revenue</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Usually "Net Profit" for owners. Investors may prefer revenue-based.</p>
                    </div>
                </div>

                {{-- Investor-specific fields --}}
                <div x-show="type === 'investor'" class="space-y-4 border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Investor Agreement</p>
                    <div>
                        <label class="form-label">Contribution Amount ({{ $sym }})</label>
                        <input type="number" name="contribution_amount" step="0.01" min="0"
                            class="form-input" value="{{ old('contribution_amount','0') }}"
                            placeholder="Total funds invested">
                        <p class="text-xs text-gray-400 mt-1">For reference and ROI calculations only.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Agreement Start</label>
                            <input type="date" name="agreement_start_date" class="form-input"
                                value="{{ old('agreement_start_date', now()->toDateString()) }}">
                        </div>
                        <div>
                            <label class="form-label">Agreement End</label>
                            <input type="date" name="agreement_end_date" class="form-input"
                                value="{{ old('agreement_end_date') }}"
                                placeholder="Leave blank for indefinite">
                            <p class="text-xs text-gray-400 mt-1">Leave blank = no end date.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-input" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="{{ route('owners.list') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary" x-text="type === 'investor' ? 'Add Investor' : 'Add Owner'">Add Owner</button>
        </div>
    </form>
</div>
@endsection
