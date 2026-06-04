<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->id }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
        body { font-family: 'Courier New', monospace; max-width: 320px; margin: 0 auto; padding: 20px; }
        .divider { border-top: 1px dashed #999; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; margin: 2px 0; font-size: 13px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .total-row { font-size: 15px; font-weight: bold; }
    </style>
</head>
<body>

    {{-- Action buttons (hidden on print) --}}
    <div class="no-print" style="display:flex; gap:8px; margin-bottom:16px;">
        <button onclick="window.print()" style="padding:6px 14px; background:#111; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:13px;">
            Print Receipt
        </button>
        <a href="{{ route('sales.create') }}" style="padding:6px 14px; background:#fff; border:1px solid #ccc; border-radius:4px; text-decoration:none; color:#333; font-size:13px;">
            New Sale
        </a>
    </div>

    {{-- Header --}}
    <div class="center">
        <div class="bold" style="font-size:18px;">{{ $shopName }}</div>
        @if($shopAddress)<div style="font-size:12px; color:#666;">{{ $shopAddress }}</div>@endif
        @if($shopPhone)<div style="font-size:12px;">Tel: {{ $shopPhone }}</div>@endif
        @if($receiptHeader)<div style="font-size:12px; margin-top:4px;">{{ $receiptHeader }}</div>@endif
    </div>

    <div class="divider"></div>

    <div class="row">
        <span>Receipt #{{ $sale->id }}</span>
        <span>{{ $sale->created_at->format('m/d/Y H:i') }}</span>
    </div>
    <div class="row">
        <span>Cashier: {{ $sale->user?->name }}</span>
    </div>
    @if($sale->customer)
    <div class="row"><span>Customer: {{ $sale->customer->name }}</span></div>
    @endif

    <div class="divider"></div>

    {{-- Items --}}
    @foreach($sale->items as $item)
    <div style="margin-bottom:4px;">
        <div class="bold" style="font-size:13px;">{{ $item->product_name }}</div>
        <div class="row" style="color:#444;">
            <span>{{ $item->quantity }} × {{ $currencySymbol }}{{ number_format($item->unit_price/100,2) }}</span>
            <span>{{ $currencySymbol }}{{ number_format($item->total/100,2) }}</span>
        </div>
        @if($item->discount_amount > 0)
        <div class="row" style="font-size:11px; color:#888;">
            <span>Discount</span><span>-{{ $currencySymbol }}{{ number_format($item->discount_amount/100,2) }}</span>
        </div>
        @endif
    </div>
    @endforeach

    <div class="divider"></div>

    <div class="row"><span>Subtotal</span><span>{{ $currencySymbol }}{{ number_format($sale->subtotal_amount/100,2) }}</span></div>
    @if($sale->tax_amount > 0)
    <div class="row"><span>{{ $taxName }}</span><span>{{ $currencySymbol }}{{ number_format($sale->tax_amount/100,2) }}</span></div>
    @endif
    @if($sale->discount_amount > 0)
    <div class="row"><span>Discount</span><span>-{{ $currencySymbol }}{{ number_format($sale->discount_amount/100,2) }}</span></div>
    @endif
    <div class="divider"></div>
    <div class="row total-row"><span>TOTAL</span><span>{{ $currencySymbol }}{{ number_format($sale->final_amount/100,2) }}</span></div>
    <div class="divider"></div>

    {{-- Payments --}}
    @foreach($sale->payments as $payment)
    <div class="row">
        <span>{{ ucfirst(str_replace('_',' ',$payment->payment_method)) }}</span>
        <span>{{ $currencySymbol }}{{ number_format($payment->amount/100,2) }}</span>
    </div>
    @endforeach

    @php
        $totalPaid = $sale->payments->sum('amount');
        $change = max(0, $totalPaid - $sale->final_amount);
    @endphp
    @if($change > 0)
    <div class="row bold"><span>Change</span><span>{{ $currencySymbol }}{{ number_format($change/100,2) }}</span></div>
    @endif

    @if($receiptFooter)
    <div class="divider"></div>
    <div class="center" style="font-size:12px; color:#666;">{{ $receiptFooter }}</div>
    @endif

    <div class="divider"></div>
    <div class="center" style="font-size:11px; color:#999;">Thank you for your purchase!</div>

    <script>
        // Auto-print when loaded directly (not from history.back)
        if (document.referrer.includes('/sales/') || !document.referrer) {
            // only print if user navigated here fresh
        }
    </script>
</body>
</html>
