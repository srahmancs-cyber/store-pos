@extends('layouts.app')
@section('title', 'Add Bill')
@section('breadcrumb')
    <a href="{{ route('finances.bills.index') }}" class="text-gray-500 hover:text-gray-900">Bills</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Add Bill</span>
@endsection
@section('content')
<div class="pt-6 max-w-lg">
    <h1 class="mb-6">Add Bill</h1>
    <form method="POST" action="{{ route('finances.bills.store') }}" class="space-y-5">
        @csrf
        <div class="card">
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Description <span class="text-red-500">*</span></label>
                    <input type="text" name="description" class="form-input @error('description') border-red-400 @enderror"
                        value="{{ old('description') }}" required placeholder="e.g. Monthly rent">
                    @error('description')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Amount <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01"
                            class="form-input @error('amount') border-red-400 @enderror"
                            value="{{ old('amount') }}" required>
                        @error('amount')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Due Date <span class="text-red-500">*</span></label>
                        <input type="date" name="due_date" class="form-input @error('due_date') border-red-400 @enderror"
                            value="{{ old('due_date') }}" required>
                        @error('due_date')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="{{ route('finances.bills.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create Bill</button>
        </div>
    </form>
</div>
@endsection
