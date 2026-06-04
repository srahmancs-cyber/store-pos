@extends('layouts.app')
@section('title', 'Quotation #{{ $quotation->id }}')
@section('breadcrumb')
    <a href="{{ route('quotations.index') }}" class="text-gray-500 hover:text-gray-900">Quotations</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">#{{ $quotation->id }}</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol', '$'); @endphp
<div class="pt-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1>Quotation #{{ $quotation->id }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $quotation->created_at->format('M d, Y H:i') }}</p>
        </div>
        <div class="flex gap-2">
            @if($quotation->status === 'converted')
                <span class="badge badge-green text-sm px-3 py-1">Converted to Sale</span>
            @else
                <button onclick="window.print()" class="btn-secondary">
                    <i data-lucide="printer" class="w-4 h-4"></i> Print
                </button>
                <form method="POST" action="{{ route('quotations.convert', $quotation) }}">
                    @csrf
                    <button class="btn-primary">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i> Convert to Sale
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2">
            <div class="card">
                <div class="card-header"><h3>Items</h3></div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead><tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr></thead>
                        <tbody>
                            @php $cartData = is_array($quotation->cart_data) ? $quotation->cart_data : []; @endphp
                            @forelse($cartData as $item)
                            <tr>
                                <td class="font-medium">{{ $item['name'] ?? '—' }}</td>
                                <td>{{ $item['qty'] ?? $item['quantity'] ?? 1 }}</td>
                                <td>{{ $sym }}{{ number_format(($item['price'] ?? 0) / 100, 2) }}</td>
                                <td class="font-medium">
                                    {{ $sym }}{{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1) / 100, 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-gray-400 py-4">No items.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-200">
                                <td colspan="3" class="px-4 py-3 text-right font-semibold">Subtotal</td>
                                <td class="px-4 py-3 font-bold">
                                    {{ $sym }}{{ number_format(collect($cartData)->sum(fn($i) => ($i['price'] ?? 0) * ($i['qty'] ?? 1)) / 100, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="card">
                <div class="card-header"><h3>Details</h3></div>
                <div class="card-body space-y-3 text-sm">
                    <div>
                        <p class="text-gray-500">Status</p>
                        @if($quotation->status === 'open') <span class="badge badge-gray">Open</span>
                        @elseif($quotation->status === 'sent') <span class="badge badge-blue">Sent</span>
                        @elseif($quotation->status === 'converted') <span class="badge badge-green">Converted</span>
                        @elseif($quotation->status === 'expired') <span class="badge badge-red">Expired</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-gray-500">Reference</p>
                        <p class="font-medium">{{ $quotation->customer_reference ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Customer</p>
                        <p class="font-medium">{{ $quotation->customer?->name ?? 'Walk-in' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Created By</p>
                        <p>{{ $quotation->user?->name }}</p>
                    </div>
                    @if($quotation->expires_at)
                    <div>
                        <p class="text-gray-500">Expires</p>
                        <p class="{{ $quotation->expires_at->isPast() ? 'text-red-600 font-medium' : '' }}">
                            {{ $quotation->expires_at->format('M d, Y') }}
                            @if($quotation->expires_at->isPast()) <span class="badge badge-red ml-1">Expired</span> @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
