@extends('layouts.app')
@section('title','Edit')
@section('breadcrumb')
    <a href="{{ route('owners.list') }}" class="text-gray-500 hover:text-gray-900">Owner List</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Edit — {{ $owner->name }}</span>
@endsection
@section('content')
@php
    $sym = \App\Models\Setting::get('currency_symbol','$');
    $currentTotal = \App\Models\Owner::where('is_active', true)->where('id','!=',$owner->id)->sum('profit_share_percentage');
@endphp
<div class="pt-6 max-w-xl" x-data="{ type: '{{ old('type', $owner->type ?? 'owner') }}' }">
    <h1 class="mb-6">Edit — {{ $owner->name }}</h1>
    <form method="POST" action="{{ route('owners.update', $owner) }}" class="space-y-5">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Type</label>
                    <div class="flex gap-4 mt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="type" value="owner" x-model="type"> Owner
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="type" value="investor" x-model="type"> Investor
                        </label>
                    </div>
                </div>
                <div>
                    <label class="form-label">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $owner->name) }}" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Profit Share %</label>
                        <input type="number" name="profit_share_percentage" step="0.01" min="0" max="100"
                            class="form-input" value="{{ old('profit_share_percentage', $owner->profit_share_percentage) }}" required>
                        <p class="text-xs text-gray-400 mt-1">Others hold {{ number_format($currentTotal,2) }}%</p>
                    </div>
                    <div>
                        <label class="form-label">Profit Basis</label>
                        <select name="profit_basis" class="form-select">
                            <option value="net_profit" {{ old('profit_basis',$owner->profit_basis ?? 'net_profit')==='net_profit'?'selected':'' }}>% of Net Profit</option>
                            <option value="sales_revenue" {{ old('profit_basis',$owner->profit_basis)==='sales_revenue'?'selected':'' }}>% of Sales Revenue</option>
                        </select>
                    </div>
                </div>

                {{-- Investor fields --}}
                <div x-show="type === 'investor'" class="space-y-4 border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Investor Agreement</p>
                    <div>
                        <label class="form-label">Contribution Amount ({{ $sym }})</label>
                        <input type="number" name="contribution_amount" step="0.01" min="0" class="form-input"
                            value="{{ old('contribution_amount', $owner->contribution_amount > 0 ? number_format($owner->contribution_amount/100,2,'.','') : '') }}">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Agreement Start</label>
                            <input type="date" name="agreement_start_date" class="form-input"
                                value="{{ old('agreement_start_date', $owner->agreement_start_date?->toDateString()) }}">
                        </div>
                        <div>
                            <label class="form-label">Agreement End</label>
                            <input type="date" name="agreement_end_date" class="form-input"
                                value="{{ old('agreement_end_date', $owner->agreement_end_date?->toDateString()) }}">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-1">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active', $owner->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
                    <label for="is_active" class="text-sm">Active (included in profit allocation)</label>
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-input" rows="2">{{ old('notes', $owner->notes) }}</textarea>
                </div>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="{{ route('owners.list') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
