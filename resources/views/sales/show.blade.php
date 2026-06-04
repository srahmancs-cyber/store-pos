@extends('layouts.app')
@section('title','Sale #{{ $sale->id }}')
@section('breadcrumb')
    <a href="{{ route('sales.index') }}" class="text-gray-500 hover:text-gray-900">Sales</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Sale #{{ $sale->id }}</span>
@endsection
@section('content')
<div class="pt-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1>Sale #{{ $sale->id }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $sale->created_at->format('F d, Y H:i') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($sale->status === 'completed') <span class="badge badge-green">Completed</span>
            @elseif($sale->status === 'voided') <span class="badge badge-red">Voided</span>
            @endif

            @if($sale->status === 'completed')
            <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="btn-secondary">
                <i data-lucide="printer" class="w-4 h-4"></i> Print Receipt
            </a>
            @if(in_array(auth()->user()->role, ['admin','manager']))
            <a href="{{ route('sales.refund', $sale) }}" class="btn-secondary">
                <i data-lucide="corner-up-left" class="w-4 h-4"></i> Refund
            </a>
            <form method="POST" action="{{ route('sales.void', $sale) }}"
                onsubmit="return confirm('Void this sale? This will restore stock for all items.')">
                @csrf
                <button type="submit" class="btn-danger">
                    <i data-lucide="x-circle" class="w-4 h-4"></i> Void Sale
                </button>
            </form>
            @endif
            @endif
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-5">
            <div class="card">
                <div class="card-header"><h3>Items</h3></div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead><tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Discount</th>
                            <th>Tax</th>
                            <th>Total</th>
                        </tr></thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr>
                                <td class="font-medium">{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $currencySymbol }}{{ number_format($item->unit_price/100,2) }}</td>
                                <td class="text-gray-500">{{ $item->discount_amount > 0 ? $currencySymbol.number_format($item->discount_amount/100,2) : '—' }}</td>
                                <td class="text-gray-500">{{ $item->tax_amount > 0 ? $currencySymbol.number_format($item->tax_amount/100,2) : '—' }}</td>
                                <td class="font-medium">{{ $currencySymbol }}{{ number_format($item->total/100,2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Payments</h3></div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead><tr>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Reference</th>
                        </tr></thead>
                        <tbody>
                            @foreach($sale->payments as $payment)
                            <tr>
                                <td class="capitalize font-medium">{{ str_replace('_',' ',$payment->payment_method) }}</td>
                                <td>{{ $currencySymbol }}{{ number_format($payment->amount/100,2) }}</td>
                                <td class="text-gray-500">{{ $payment->reference_number ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="card">
                <div class="card-header"><h3>Summary</h3></div>
                <div class="card-body space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span>{{ $currencySymbol }}{{ number_format($sale->subtotal_amount/100,2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Tax</span>
                        <span>{{ $currencySymbol }}{{ number_format($sale->tax_amount/100,2) }}</span>
                    </div>
                    @if($sale->discount_amount > 0)
                    <div class="flex justify-between text-gray-600">
                        <span>Discount</span>
                        <span>-{{ $currencySymbol }}{{ number_format($sale->discount_amount/100,2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-semibold text-gray-900 text-base pt-2 border-t border-gray-100">
                        <span>Total</span>
                        <span>{{ $currencySymbol }}{{ number_format($sale->final_amount/100,2) }}</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Details</h3></div>
                <div class="card-body space-y-3 text-sm">
                    <div>
                        <p class="text-gray-500">Cashier</p>
                        <p class="font-medium">{{ $sale->user?->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Customer</p>
                        <p class="font-medium">{{ $sale->customer?->name ?? 'Walk-in' }}</p>
                    </div>
                    @if($sale->notes)
                    <div>
                        <p class="text-gray-500">Notes</p>
                        <p>{{ $sale->notes }}</p>
                    </div>
                    @endif
                    @if($sale->voided_at)
                    <div class="pt-2 border-t border-gray-100">
                        <p class="text-gray-500">Voided By</p>
                        <p class="text-red-600">{{ $sale->voidedByUser?->name }} at {{ $sale->voided_at->format('M d, Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
