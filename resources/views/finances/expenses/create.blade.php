@extends('layouts.app')
@section('title', 'Add Expense')
@section('breadcrumb')
    <a href="{{ route('finances.expenses.index') }}" class="text-gray-500 hover:text-gray-900">Expenses</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Add Expense</span>
@endsection

@section('content')
<div class="pt-6 max-w-xl">
    <h1 class="mb-6">Add Expense</h1>

    <form method="POST" action="{{ route('finances.expenses.store') }}"
          enctype="multipart/form-data"
          class="space-y-5"
          x-data="{ isRecurring: {{ old('is_recurring') ? 'true' : 'false' }} }">
        @csrf

        <div class="card">
            <div class="card-body space-y-4">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Amount <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01"
                            class="form-input @error('amount') border-red-400 @enderror"
                            value="{{ old('amount') }}" required>
                        @error('amount')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="date"
                            class="form-input @error('date') border-red-400 @enderror"
                            value="{{ old('date', now()->toDateString()) }}" required>
                        @error('date')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Category <span class="text-red-500">*</span></label>
                        <select name="category" class="form-select @error('category') border-red-400 @enderror" required>
                            <option value="">Select category</option>
                            @foreach(['utilities','rent','internet','marketing','repairs','supplies','other'] as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                    {{ ucfirst($cat) }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" maxlength="500"
                        class="form-input @error('description') border-red-400 @enderror"
                        placeholder="Brief description of the expense">{{ old('description') }}</textarea>
                    @error('description')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Receipt Image <span class="text-xs text-gray-400">(optional, max 2MB)</span></label>
                    <input type="file" name="receipt_image" accept="image/*" class="form-input">
                    @error('receipt_image')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="border-t border-gray-100 pt-4 space-y-3">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="is_recurring" name="is_recurring" value="1"
                            x-model="isRecurring"
                            {{ old('is_recurring') ? 'checked' : '' }}
                            class="rounded border-gray-300">
                        <label for="is_recurring" class="text-sm text-gray-700">This is a recurring expense (repeats monthly)</label>
                    </div>

                    <div x-show="isRecurring" x-cloak>
                        <label class="form-label">Day of Month (1–28)</label>
                        <input type="number" name="recurring_day_of_month" min="1" max="28"
                            class="form-input w-24"
                            value="{{ old('recurring_day_of_month', now()->day) }}"
                            placeholder="1">
                        <p class="text-xs text-gray-400 mt-1">A new expense will be auto-created on this day each month.</p>
                        @error('recurring_day_of_month')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('finances.expenses.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Expense</button>
        </div>

    </form>
</div>
@endsection
