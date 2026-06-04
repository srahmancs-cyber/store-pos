@extends('layouts.app')
@section('title','Edit Promo Code')
@section('breadcrumb')
    <a href="{{ route('promo-codes.index') }}" class="text-gray-500 hover:text-gray-900">Promo Codes</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">{{ $promoCode->code }}</span>
@endsection
@section('content')
<div class="pt-6 max-w-xl" x-data="{ discountType: '{{ old('discount_type', $promoCode->discount_type) }}' }">
    <h1 class="mb-6">Edit Promo Code — {{ $promoCode->code }}</h1>
    <div class="alert alert-info mb-4">
        <i data-lucide="info" class="w-4 h-4 flex-shrink-0"></i>
        Used {{ $promoCode->used_count }} time(s) so far. Code cannot be renamed.
    </div>
    <form method="POST" action="{{ route('promo-codes.update', $promoCode) }}" class="space-y-5">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Discount Type</label>
                        <select name="discount_type" x-model="discountType" class="form-select">
                            <option value="percentage" {{ old('discount_type',$promoCode->discount_type)==='percentage'?'selected':'' }}>Percentage (%)</option>
                            <option value="fixed" {{ old('discount_type',$promoCode->discount_type)==='fixed'?'selected':'' }}>Fixed Amount</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Discount Value</label>
                        <input type="number" name="discount_value" step="0.01" min="0.01" class="form-input"
                            value="{{ old('discount_value', $promoCode->discount_type === 'percentage'
                                ? number_format($promoCode->discount_value / 100, 2)
                                : number_format($promoCode->discount_value / 100, 2)) }}" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Min Order Amount ({{ $sym }})</label>
                        <input type="number" name="min_order_amount" step="0.01" min="0" class="form-input"
                            value="{{ old('min_order_amount', number_format($promoCode->min_order_amount/100,2,'.','')) }}">
                    </div>
                    <div>
                        <label class="form-label">Max Uses</label>
                        <input type="number" name="max_uses" min="1" class="form-input"
                            value="{{ old('max_uses', $promoCode->max_uses) }}" placeholder="Unlimited">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Valid From</label>
                        <input type="date" name="starts_at" class="form-input"
                            value="{{ old('starts_at', $promoCode->starts_at?->toDateString()) }}">
                    </div>
                    <div>
                        <label class="form-label">Expires On</label>
                        <input type="date" name="expires_at" class="form-input"
                            value="{{ old('expires_at', $promoCode->expires_at?->toDateString()) }}">
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active', $promoCode->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
                    <label for="is_active" class="text-sm">Active</label>
                </div>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="{{ route('promo-codes.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
