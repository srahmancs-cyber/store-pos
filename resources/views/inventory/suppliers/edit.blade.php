@extends('layouts.app')
@section('title','Edit Supplier')
@section('breadcrumb')
    <a href="{{ route('inventory.suppliers.index') }}" class="text-gray-500 hover:text-gray-900">Suppliers</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Edit</span>
@endsection
@section('content')
<div class="pt-6 max-w-xl">
    <h1 class="mb-6">Edit Supplier</h1>
    <form method="POST" action="{{ route('inventory.suppliers.update', $supplier) }}" class="space-y-5">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Company Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input" required value="{{ old('name', $supplier->name) }}">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-input" value="{{ old('contact_person', $supplier->contact_person) }}">
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-input" value="{{ old('phone', $supplier->phone) }}">
                    </div>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $supplier->email) }}">
                </div>
                <div>
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-input" rows="2">{{ old('address', $supplier->address) }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Lead Time (days)</label>
                        <input type="number" name="lead_time_days" class="form-input" min="1" max="365"
                            value="{{ old('lead_time_days', $supplier->lead_time_days) }}">
                    </div>
                    <div class="flex items-center gap-3 pt-6">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                            {{ old('is_active', $supplier->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
                        <label for="is_active" class="text-sm">Active</label>
                    </div>
                </div>
                <div>
                    <label class="form-label">Payment Terms</label>
                    <textarea name="payment_terms" class="form-input" rows="2">{{ old('payment_terms', $supplier->payment_terms) }}</textarea>
                </div>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="{{ route('inventory.suppliers.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
