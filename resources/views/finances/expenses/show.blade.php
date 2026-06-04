@extends('layouts.app')
@section('title', 'Expense Detail')
@section('breadcrumb')
    <a href="{{ route('finances.expenses.index') }}" class="text-gray-500 hover:text-gray-900">Expenses</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Expense #{{ $expense->id }}</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 max-w-xl space-y-5">
    <div class="flex items-center justify-between">
        <h1>Expense #{{ $expense->id }}</h1>
        <a href="{{ route('finances.expenses.edit', $expense) }}" class="btn-secondary">Edit</a>
    </div>
    <div class="card">
        <div class="card-body grid grid-cols-2 gap-4 text-sm">
            <div><p class="text-gray-500">Amount</p><p class="text-xl font-bold mt-0.5">{{ $sym }}{{ number_format($expense->amount/100,2) }}</p></div>
            <div><p class="text-gray-500">Date</p><p class="font-medium mt-0.5">{{ \Carbon\Carbon::parse($expense->date)->format('M d, Y') }}</p></div>
            <div><p class="text-gray-500">Category</p><p class="mt-0.5"><span class="badge badge-gray capitalize">{{ $expense->category }}</span></p></div>
            <div><p class="text-gray-500">Payment Method</p><p class="mt-0.5 capitalize">{{ $expense->payment_method ?? '—' }}</p></div>
            <div class="col-span-2"><p class="text-gray-500">Description</p><p class="mt-0.5">{{ $expense->description ?? '—' }}</p></div>
            <div><p class="text-gray-500">Recurring</p>
                <p class="mt-0.5">
                    @if($expense->is_recurring)
                        <span class="badge badge-blue">Day {{ $expense->recurring_day_of_month }} each month</span>
                    @else No @endif
                </p>
            </div>
            <div><p class="text-gray-500">Created By</p><p class="mt-0.5">{{ $expense->creator?->name ?? '—' }}</p></div>
        </div>
        @if($expense->receipt_image)
        <div class="px-6 pb-5">
            <p class="text-sm text-gray-500 mb-2">Receipt</p>
            <img src="{{ Storage::url($expense->receipt_image) }}" class="max-w-xs rounded border border-gray-200">
        </div>
        @endif
    </div>
</div>
@endsection
