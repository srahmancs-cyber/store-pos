@extends('layouts.app')
@section('title','Edit Customer')
@section('breadcrumb')
    <a href="{{ route('customers.index') }}" class="text-gray-500 hover:text-gray-900">Customers</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Edit</span>
@endsection
@section('content')
<div class="pt-6 max-w-lg">
    <h1 class="mb-6">Edit Customer</h1>
    <form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-5">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body space-y-4">
                <div><label class="form-label">Name</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $customer->name) }}"></div>
                <div><label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-input" value="{{ old('phone', $customer->phone) }}"></div>
                <div><label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $customer->email) }}">
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror</div>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="{{ route('customers.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
