@extends('layouts.app')
@section('title', 'Process Refund — Sale #{{ $sale->id }}')
@section('breadcrumb')
    <a href="{{ route('sales.index') }}" class="text-gray-500 hover:text-gray-900">Sales</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <a href="{{ route('sales.show', $sale) }}" class="text-gray-500 hover:text-gray-900">Sale #{{ $sale->id }}</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Refund</span>
@endsection
@section('content')
<div class="pt-6 max-w-2xl space-y-5">
    <div class="flex items-center justify-between">
        <h1>Process Refund — Sale #{{ $sale->id }}</h1>
    </div>

    {{-- Sale Summary --}}
    <div class="card">
        <div class="card-header"><h3>Sale Items</h3></div>
        <div class="table-wrapper">
            <table class="table">
                <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
                <tbody>
                    @foreach($sale->items as $item)
                    <tr>
                        <td class="font-medium">{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $sym }}{{ number_format($item->unit_price/100,2) }}</td>
                        <td>{{ $sym }}{{ number_format($item->total/100,2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-body pt-3 pb-4 border-t border-gray-100 text-sm space-y-1">
            <div class="flex justify-between"><span class="text-gray-500">Sale Total</span><span class="font-semibold">{{ $sym }}{{ number_format($sale->final_amount/100,2) }}</span></div>
            @if($alreadyRefunded > 0)
            <div class="flex justify-between"><span class="text-gray-500">Already Refunded</span><span class="text-red-600">-{{ $sym }}{{ number_format($alreadyRefunded/100,2) }}</span></div>
            @endif
            <div class="flex justify-between font-bold text-base pt-1 border-t border-gray-100"><span>Max Refundable</span><span>{{ $sym }}{{ number_format($maxRefundable/100,2) }}</span></div>
        </div>
    </div>

    {{-- Refund Form --}}
    <form method="POST" action="{{ route('sales.refund.store', $sale) }}" class="space-y-5">
        @csrf
        <div class="card">
            <div class="card-header"><h3>Refund Details</h3></div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Refund Amount <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01"
                            max="{{ number_format($maxRefundable/100,2,'.','') }}"
                            class="form-input @error('amount') border-red-400 @enderror"
                            value="{{ old('amount', number_format($maxRefundable/100,2,'.','')) }}" required>
                        @error('amount')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Refund Via <span class="text-red-500">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            @foreach($sale->payments->pluck('payment_method')->unique() as $method)
                            <option value="{{ $method }}">{{ ucfirst(str_replace('_',' ',$method)) }} (original)</option>
                            @endforeach
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">Reason</label>
                    <textarea name="reason" class="form-input" rows="2" placeholder="Reason for refund (optional)">{{ old('reason') }}</textarea>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="restock" name="restock" value="1"
                        {{ old('restock', '1') ? 'checked' : '' }} class="rounded border-gray-300">
                    <label for="restock" class="text-sm text-gray-700">Return items to inventory (restock)</label>
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('sales.show', $sale) }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-danger">
                <i data-lucide="corner-up-left" class="w-4 h-4"></i> Process Refund
            </button>
        </div>
    </form>
</div>
@endsection
