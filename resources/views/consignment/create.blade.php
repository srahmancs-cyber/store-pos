@extends('layouts.app')
@section('title','Add Consignment Vendor')
@section('breadcrumb')
    <a href="{{ route('consignment.index') }}" class="text-gray-500 hover:text-gray-900">Consignment</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Add Vendor</span>
@endsection
@section('content')
<div class="pt-6 max-w-xl">
    <h1 class="mb-6">Add Consignment Vendor</h1>
    <form method="POST" action="{{ route('consignment.store') }}" class="space-y-5">
        @csrf
        <div class="card">
            <div class="card-header"><h3>Vendor Details</h3></div>
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Vendor Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input @error('name') border-red-400 @enderror"
                        value="{{ old('name') }}" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-input" value="{{ old('contact_person') }}">
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-input" value="{{ old('phone') }}">
                    </div>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email') }}">
                </div>
                <div>
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-input" rows="2">{{ old('address') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Commission Settings</h3></div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Store Commission % <span class="text-red-500">*</span></label>
                        <input type="number" name="default_commission_rate" step="0.01" min="0" max="100"
                            class="form-input @error('default_commission_rate') border-red-400 @enderror"
                            value="{{ old('default_commission_rate', '30') }}" required>
                        <p class="text-xs text-gray-400 mt-1">% that the store keeps from each sale.</p>
                        @error('default_commission_rate')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Commission Basis <span class="text-red-500">*</span></label>
                        <select name="commission_basis" class="form-select" required>
                            <option value="sale_price" {{ old('commission_basis','sale_price')==='sale_price'?'selected':'' }}>
                                % of Sale Price
                            </option>
                            <option value="profit" {{ old('commission_basis')==='profit'?'selected':'' }}>
                                % of Profit (Sale − Cost)
                            </option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">How the commission is calculated.</p>
                    </div>
                </div>
                <div>
                    <label class="form-label">Payout Frequency <span class="text-red-500">*</span></label>
                    <select name="payout_frequency" class="form-select w-56" required>
                        <option value="monthly" {{ old('payout_frequency','monthly')==='monthly'?'selected':'' }}>Monthly</option>
                        <option value="weekly" {{ old('payout_frequency')==='weekly'?'selected':'' }}>Weekly</option>
                        <option value="on_sale" {{ old('payout_frequency')==='on_sale'?'selected':'' }}>Per Sale</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-input" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('consignment.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Add Vendor</button>
        </div>
    </form>
</div>
@endsection
