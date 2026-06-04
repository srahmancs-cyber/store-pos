@extends('layouts.app')
@section('title','New Promo Code')
@section('breadcrumb')
    <a href="{{ route('promo-codes.index') }}" class="text-gray-500 hover:text-gray-900">Promo Codes</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">New Code</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 max-w-xl" x-data="{ discountType: '{{ old('discount_type','percentage') }}' }">
    <h1 class="mb-6">New Promo Code</h1>
    <form method="POST" action="{{ route('promo-codes.store') }}" class="space-y-5">
        @csrf
        <div class="card">
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" class="form-input uppercase @error('code') border-red-400 @enderror"
                        value="{{ old('code') }}" placeholder="SUMMER20" maxlength="50" required
                        style="text-transform:uppercase">
                    <p class="text-xs text-gray-400 mt-1">Letters, numbers, and hyphens only. Will be uppercased automatically.</p>
                    @error('code')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Discount Type <span class="text-red-500">*</span></label>
                        <select name="discount_type" x-model="discountType" class="form-select" required>
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">
                            Discount Value <span class="text-red-500">*</span>
                            <span x-show="discountType==='percentage'" class="text-gray-400 font-normal">(0–100%)</span>
                            <span x-show="discountType==='fixed'" class="text-gray-400 font-normal">({{ $sym }})</span>
                        </label>
                        <input type="number" name="discount_value" step="0.01" min="0.01"
                            :max="discountType==='percentage' ? 100 : undefined"
                            class="form-input @error('discount_value') border-red-400 @enderror"
                            value="{{ old('discount_value') }}" required>
                        @error('discount_value')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Min Order Amount ({{ $sym }})</label>
                        <input type="number" name="min_order_amount" step="0.01" min="0"
                            class="form-input" value="{{ old('min_order_amount', '0') }}" placeholder="0.00">
                    </div>
                    <div>
                        <label class="form-label">Max Uses <span class="text-gray-400 font-normal text-xs">(blank = unlimited)</span></label>
                        <input type="number" name="max_uses" min="1" class="form-input"
                            value="{{ old('max_uses') }}" placeholder="Unlimited">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Valid From</label>
                        <input type="date" name="starts_at" class="form-input" value="{{ old('starts_at') }}">
                    </div>
                    <div>
                        <label class="form-label">Expires On</label>
                        <input type="date" name="expires_at" class="form-input" value="{{ old('expires_at') }}">
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active','1') ? 'checked' : '' }} class="rounded border-gray-300">
                    <label for="is_active" class="text-sm">Active (usable at checkout)</label>
                </div>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="{{ route('promo-codes.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create Promo Code</button>
        </div>
    </form>
</div>
@endsection
