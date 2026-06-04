@extends('layouts.app')
@section('title', 'Bill Detail')
@section('breadcrumb')
    <a href="{{ route('finances.bills.index') }}" class="text-gray-500 hover:text-gray-900">Bills</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Bill #{{ $bill->id }}</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 max-w-lg space-y-5">
    <div class="flex items-center justify-between">
        <h1>Bill #{{ $bill->id }}</h1>
        @if($bill->status === 'unpaid')
        <a href="{{ route('finances.bills.edit', $bill) }}" class="btn-secondary">Edit</a>
        @endif
    </div>
    <div class="card">
        <div class="card-body grid grid-cols-2 gap-4 text-sm">
            <div><p class="text-gray-500">Description</p><p class="font-medium mt-0.5">{{ $bill->description }}</p></div>
            <div><p class="text-gray-500">Amount</p><p class="text-xl font-bold mt-0.5">{{ $sym }}{{ number_format($bill->amount/100,2) }}</p></div>
            <div><p class="text-gray-500">Due Date</p>
                <p class="mt-0.5 {{ $bill->status === 'unpaid' && $bill->due_date->isPast() ? 'text-red-600 font-medium' : '' }}">
                    {{ $bill->due_date->format('M d, Y') }}
                </p>
            </div>
            <div><p class="text-gray-500">Status</p>
                <p class="mt-0.5">
                    @if($bill->status === 'paid')<span class="badge badge-green">Paid</span>
                    @else<span class="badge badge-yellow">Unpaid</span>@endif
                </p>
            </div>
            @if($bill->paid_date)
            <div><p class="text-gray-500">Paid On</p><p class="mt-0.5">{{ $bill->paid_date->format('M d, Y') }}</p></div>
            @endif
            @if($bill->payment_method)
            <div><p class="text-gray-500">Paid Via</p><p class="mt-0.5 capitalize">{{ $bill->payment_method }}</p></div>
            @endif
            <div><p class="text-gray-500">Created By</p><p class="mt-0.5">{{ $bill->creator?->name ?? '—' }}</p></div>
        </div>
    </div>
    @if($bill->status === 'unpaid')
    <form method="POST" action="{{ route('finances.bills.pay', $bill) }}" class="card">
        @csrf
        <div class="card-body space-y-4">
            <h3>Mark as Paid</h3>
            <div>
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select w-48">
                    <option value="bank">Bank Transfer</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Confirm Payment</button>
        </div>
    </form>
    @endif
    <a href="{{ route('finances.bills.index') }}" class="btn-secondary inline-flex">Back to Bills</a>
</div>
@endsection
