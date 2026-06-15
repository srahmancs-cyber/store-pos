@extends('layouts.app')
@section('title','Edit Consignment Vendor')
@section('breadcrumb')
    <a href="{{ route('consignment.index') }}" class="text-gray-500 hover:text-gray-900">Consignment</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Edit — {{ $consignment->name }}</span>
@endsection
@section('content')
<div class="pt-6 max-w-xl">
    <h1 class="mb-6">Edit Vendor</h1>
    <form method="POST" action="{{ route('consignment.update', $consignment) }}" class="space-y-5">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-header"><h3>Vendor Details</h3></div>
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Vendor Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $consignment->name) }}" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-input" value="{{ old('contact_person', $consignment->contact_person) }}">
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-input" value="{{ old('phone', $consignment->phone) }}">
                    </div>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $consignment->email) }}">
                </div>
                <div>
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-input" rows="2">{{ old('address', $consignment->address) }}</textarea>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active', $consignment->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
                    <label for="is_active" class="text-sm">Active</label>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3>Commission Settings</h3></div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Store Commission %</label>
                        <input type="number" name="default_commission_rate" step="0.01" min="0" max="100"
                            class="form-input" value="{{ old('default_commission_rate', $consignment->default_commission_rate) }}" required>
                    </div>
                    <div>
                        <label class="form-label">Commission Basis</label>
                        <select name="commission_basis" class="form-select" required>
                            <option value="sale_price" {{ old('commission_basis', $consignment->commission_basis)==='sale_price'?'selected':'' }}>% of Sale Price</option>
                            <option value="profit" {{ old('commission_basis', $consignment->commission_basis)==='profit'?'selected':'' }}>% of Profit</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">Payout Frequency</label>
                    <select name="payout_frequency" class="form-select w-56" required>
                        <option value="monthly" {{ old('payout_frequency', $consignment->payout_frequency)==='monthly'?'selected':'' }}>Monthly</option>
                        <option value="weekly" {{ old('payout_frequency', $consignment->payout_frequency)==='weekly'?'selected':'' }}>Weekly</option>
                        <option value="on_sale" {{ old('payout_frequency', $consignment->payout_frequency)==='on_sale'?'selected':'' }}>Per Sale</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-input" rows="2">{{ old('notes', $consignment->notes) }}</textarea>
                </div>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="{{ route('consignment.show', $consignment) }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
