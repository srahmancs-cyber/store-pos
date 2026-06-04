@extends('layouts.app')
@section('title', 'Edit Expense')
@section('breadcrumb')
    <a href="{{ route('finances.expenses.index') }}" class="text-gray-500 hover:text-gray-900">Expenses</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Edit Expense</span>
@endsection

@section('content')
<div class="pt-6 max-w-xl">
    <h1 class="mb-6">Edit Expense</h1>

    <form method="POST" action="{{ route('finances.expenses.update', $expense) }}"
          enctype="multipart/form-data"
          class="space-y-5"
          x-data="{ isRecurring: {{ $expense->is_recurring ? 'true' : 'false' }} }">
        @csrf @method('PUT')

        <div class="card">
            <div class="card-body space-y-4">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Amount <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01"
                            class="form-input"
                            value="{{ old('amount', number_format($expense->amount / 100, 2, '.', '')) }}" required>
                    </div>
                    <div>
                        <label class="form-label">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="date" class="form-input"
                            value="{{ old('date', \Carbon\Carbon::parse($expense->date)->toDateString()) }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Category <span class="text-red-500">*</span></label>
                        <select name="category" class="form-select" required>
                            @foreach(['utilities','rent','internet','marketing','repairs','supplies','other'] as $cat)
                                <option value="{{ $cat }}" {{ old('category', $expense->category) === $cat ? 'selected' : '' }}>
                                    {{ ucfirst($cat) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            @foreach(['cash' => 'Cash', 'bank' => 'Bank Transfer', 'card' => 'Card'] as $val => $label)
                                <option value="{{ $val }}" {{ old('payment_method', $expense->payment_method) === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" maxlength="500"
                        class="form-input">{{ old('description', $expense->description) }}</textarea>
                </div>

                @if($expense->receipt_image)
                <div class="flex items-center gap-3">
                    <img src="{{ Storage::url($expense->receipt_image) }}"
                        class="w-16 h-16 object-cover rounded border border-gray-200">
                    <span class="text-xs text-gray-400">Current receipt image</span>
                </div>
                @endif
                <div>
                    <label class="form-label">{{ $expense->receipt_image ? 'Replace Receipt' : 'Receipt Image' }} <span class="text-xs text-gray-400">(optional)</span></label>
                    <input type="file" name="receipt_image" accept="image/*" class="form-input">
                </div>

                <div class="border-t border-gray-100 pt-4 space-y-3">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="is_recurring" name="is_recurring" value="1"
                            x-model="isRecurring"
                            {{ old('is_recurring', $expense->is_recurring) ? 'checked' : '' }}
                            class="rounded border-gray-300">
                        <label for="is_recurring" class="text-sm text-gray-700">Recurring monthly expense</label>
                    </div>
                    <div x-show="isRecurring" x-cloak>
                        <label class="form-label">Day of Month (1–28)</label>
                        <input type="number" name="recurring_day_of_month" min="1" max="28"
                            class="form-input w-24"
                            value="{{ old('recurring_day_of_month', $expense->recurring_day_of_month) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('finances.expenses.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
