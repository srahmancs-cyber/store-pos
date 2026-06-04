@extends('layouts.app')
@section('title', 'Edit Bill')
@section('breadcrumb')
    <a href="{{ route('finances.bills.index') }}" class="text-gray-500 hover:text-gray-900">Bills</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Edit Bill</span>
@endsection

@section('content')
<div class="pt-6 max-w-lg">
    <h1 class="mb-6">Edit Bill</h1>

    <form method="POST" action="{{ route('finances.bills.update', $bill) }}" class="space-y-5">
        @csrf @method('PUT')

        <div class="card">
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Description <span class="text-red-500">*</span></label>
                    <input type="text" name="description" class="form-input" required
                        value="{{ old('description', $bill->description) }}">
                    @error('description')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Amount <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01" class="form-input" required
                            value="{{ old('amount', number_format($bill->amount / 100, 2, '.', '')) }}">
                        @error('amount')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Due Date <span class="text-red-500">*</span></label>
                        <input type="date" name="due_date" class="form-input" required
                            value="{{ old('due_date', \Carbon\Carbon::parse($bill->due_date)->toDateString()) }}">
                        @error('due_date')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('finances.bills.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
