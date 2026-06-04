@extends('layouts.app')
@section('title', 'New Sale')
@section('breadcrumb')
    <span class="text-gray-500">Sales</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="text-gray-900 font-medium">New Sale</span>
@endsection

@section('content')
@php
    $sym        = $currencySymbol;
    $taxRateVal = (float) $taxRate;
    $inclusive  = $taxInclusive ? 'true' : 'false';
    $restoredCart = session('restored_cart', null);
@endphp

<div class="pt-4"
    x-data="posApp({{ json_encode($products) }}, {{ $taxRateVal }}, {{ $inclusive }}, '{{ $sym }}')"
    x-init="init()">

    <div class="flex gap-4 h-[calc(100vh-120px)]">

        {{-- LEFT: Product Search + Cart --}}
        <div class="flex-1 flex flex-col gap-3 min-w-0">

            {{-- Search Bar --}}
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="text"
                    x-model="search"
                    @input.debounce.200ms="searchProducts()"
                    @keydown.escape="searchResults = []"
                    placeholder="Search by name, SKU or scan barcode…"
                    class="form-input pl-9 w-full text-base"
                    autofocus>
                {{-- Search Results Dropdown --}}
                <div x-show="searchResults.length > 0" x-cloak
                    class="absolute z-50 top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-72 overflow-y-auto">
                    <template x-for="p in searchResults" :key="p.id">
                        <div @click="addToCart(p); search=''; searchResults=[]"
                            class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0">
                            <div>
                                <p class="font-medium text-sm text-gray-900" x-text="p.name"></p>
                                <p class="text-xs text-gray-400" x-text="'SKU: ' + p.sku + (p.category ? ' · ' + p.category : '')"></p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-sm" x-text="'{{ $sym }}' + (p.selling_price/100).toFixed(2)"></p>
                                <p class="text-xs" :class="p.current_stock <= 0 ? 'text-red-500' : 'text-gray-400'" x-text="'Stock: ' + p.current_stock"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Cart Table --}}
            <div class="card flex-1 overflow-hidden flex flex-col">
                <div class="card-header flex items-center justify-between py-3">
                    <h3>Cart</h3>
                    <div class="flex gap-2">
                        <a href="{{ route('sales.held') }}" class="btn btn-secondary btn-sm">
                            <i data-lucide="bookmark" class="w-3.5 h-3.5"></i> Held Carts
                        </a>
                        <button @click="holdCart()" class="btn btn-secondary btn-sm">
                            <i data-lucide="pause" class="w-3.5 h-3.5"></i> Hold
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <table class="table">
                        <thead class="sticky top-0 bg-white">
                            <tr>
                                <th>Product</th>
                                <th class="w-24">Qty</th>
                                <th class="w-28">Unit Price</th>
                                <th class="w-28">Total</th>
                                <th class="w-8"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="cart.length === 0">
                                <tr>
                                    <td colspan="5" class="text-center text-gray-400 py-12">
                                        <i data-lucide="shopping-cart" class="w-8 h-8 mx-auto mb-2 text-gray-200"></i>
                                        <p>Cart is empty — search for a product to begin</p>
                                    </td>
                                </tr>
                            </template>
                            <template x-for="(item, index) in cart" :key="index">
                                <tr>
                                    <td>
                                        <p class="font-medium text-sm" x-text="item.name"></p>
                                        <p class="text-xs text-gray-400" x-text="item.sku"></p>
                                        <template x-if="item.has_serial">
                                            <input type="text" x-model="item.serial_number"
                                                placeholder="Serial number"
                                                class="mt-1 form-input text-xs py-0.5">
                                        </template>
                                    </td>
                                    <td>
                                        <input type="number" x-model.number="item.qty"
                                            @change="item.qty = Math.max(1, Math.min(item.qty, item.stock))"
                                            min="1" :max="item.stock"
                                            class="form-input w-20 text-center">
                                    </td>
                                    <td x-text="'{{ $sym }}' + (item.price/100).toFixed(2)"></td>
                                    <td class="font-medium" x-text="'{{ $sym }}' + (item.price * item.qty / 100).toFixed(2)"></td>
                                    <td>
                                        <button @click="cart.splice(index,1)" class="text-gray-300 hover:text-red-500">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Discount Row --}}
                <div class="border-t border-gray-100 px-4 py-3 flex items-center gap-3">
                    <span class="text-sm text-gray-500 flex-shrink-0">Discount</span>
                    <div class="flex items-center gap-2">
                        <select x-model="discountType" class="form-select text-xs py-1 w-24">
                            <option value="fixed">Fixed {{ $sym }}</option>
                            <option value="percent">Percent %</option>
                        </select>
                        <input type="number" x-model.number="discountValue" min="0" step="0.01"
                            placeholder="0" class="form-input w-24 text-xs py-1">
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        <span class="text-sm text-gray-500 flex-shrink-0">Promo Code</span>
                        <input type="text" x-model="promoCode" placeholder="CODE"
                            class="form-input w-28 text-xs py-1 uppercase">
                        <button @click="applyPromo()" class="btn btn-secondary btn-sm text-xs">Apply</button>
                    </div>
                    <p x-show="promoMessage" class="text-xs ml-2"
                        :class="promoValid ? 'text-green-600' : 'text-red-500'" x-text="promoMessage"></p>
                </div>
            </div>
        </div>

        {{-- RIGHT: Order Summary + Payment --}}
        <div class="w-80 flex-shrink-0 flex flex-col gap-3">

            {{-- Customer --}}
            <div class="card">
                <div class="card-body py-3 space-y-2">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Customer (optional)</p>
                    <input type="text" x-model="customerPhone" placeholder="Phone" class="form-input text-sm">
                    <input type="text" x-model="customerEmail" placeholder="Email" class="form-input text-sm">
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="card">
                <div class="card-body py-3 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span x-text="'{{ $sym }}' + (subtotal/100).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-gray-600" x-show="taxAmount > 0">
                        <span>{{ $taxName }} ({{ $taxRate }}%)</span>
                        <span x-text="'{{ $sym }}' + (taxAmount/100).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-gray-600" x-show="discountAmount > 0">
                        <span>Discount</span>
                        <span class="text-red-500" x-text="'-{{ $sym }}' + (discountAmount/100).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between font-bold text-base pt-2 border-t border-gray-100">
                        <span>Total</span>
                        <span x-text="'{{ $sym }}' + (total/100).toFixed(2)"></span>
                    </div>
                </div>
            </div>

            {{-- Payments --}}
            <div class="card flex-1">
                <div class="card-header py-3 flex items-center justify-between">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</p>
                    <button @click="addPaymentLine()" class="btn btn-secondary btn-sm text-xs">
                        <i data-lucide="plus" class="w-3 h-3"></i> Split
                    </button>
                </div>
                <div class="card-body py-3 space-y-2">
                    <template x-for="(pmt, i) in payments" :key="i">
                        <div class="flex gap-2 items-center">
                            <select x-model="pmt.method" class="form-select text-sm flex-1">
                                @foreach($paymentMethods as $method)
                                <option value="{{ $method }}">{{ ucfirst(str_replace('_',' ',$method)) }}</option>
                                @endforeach
                            </select>
                            <input type="number" x-model.number="pmt.amount" min="0" step="0.01"
                                placeholder="0.00" class="form-input text-sm w-28">
                            <button x-show="payments.length > 1" @click="payments.splice(i,1)"
                                class="text-gray-300 hover:text-red-400 flex-shrink-0">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </template>

                    <div class="pt-2 border-t border-gray-100 text-xs space-y-1">
                        <div class="flex justify-between text-gray-500">
                            <span>Amount tendered</span>
                            <span x-text="'{{ $sym }}' + (totalPaid/100).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between font-semibold" x-show="changeAmount > 0">
                            <span>Change</span>
                            <span class="text-green-600" x-text="'{{ $sym }}' + (changeAmount/100).toFixed(2)"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <textarea x-model="notes" placeholder="Notes (optional)" rows="2"
                class="form-input text-sm resize-none"></textarea>

            {{-- Charge Button --}}
            <button @click="submitSale()"
                :disabled="cart.length === 0 || totalPaid < total"
                :class="cart.length > 0 && totalPaid >= total ? 'bg-gray-900 hover:bg-gray-800' : 'bg-gray-300 cursor-not-allowed'"
                class="w-full py-3.5 text-white font-semibold rounded-lg transition-colors text-sm">
                <span x-text="cart.length === 0 ? 'Add items to cart' : (totalPaid < total ? 'Insufficient payment' : 'Charge ' + '{{ $sym }}' + (total/100).toFixed(2))"></span>
            </button>
        </div>
    </div>

    {{-- Hidden form for submission --}}
    <form id="sale-form" method="POST" action="{{ route('sales.store') }}" class="hidden">
        @csrf
        <div id="sale-form-fields"></div>
    </form>

    {{-- Hold Modal --}}
    <div x-show="showHoldModal" x-cloak class="modal-backdrop" @click.self="showHoldModal=false">
        <div class="modal max-w-sm">
            <div class="modal-header">
                <h3>Hold Cart</h3>
                <button @click="showHoldModal=false"><i data-lucide="x" class="w-4 h-4 text-gray-400"></i></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Reference Name (optional)</label>
                <input type="text" x-model="holdName" placeholder="e.g. Customer John" class="form-input">
            </div>
            <div class="modal-footer">
                <button @click="showHoldModal=false" class="btn-secondary">Cancel</button>
                <button @click="confirmHold()" class="btn-primary">Hold Cart</button>
            </div>
        </div>
    </div>

</div>

<script>
function posApp(allProducts, taxRate, taxInclusive, sym) {
    return {
        allProducts,
        taxRate,
        taxInclusive,
        sym,
        cart: [],
        search: '',
        searchResults: [],
        discountType: 'fixed',
        discountValue: 0,
        promoCode: '',
        promoMessage: '',
        promoValid: false,
        promoDiscount: 0,
        payments: [{ method: '{{ $paymentMethods[0] ?? 'cash' }}', amount: 0 }],
        customerPhone: '',
        customerEmail: '',
        notes: '',
        holdName: '',
        showHoldModal: false,

        init() {
            @if($restoredCart)
            this.cart = @json($restoredCart);
            @endif
            this.$watch('total', val => {
                if (this.payments.length === 1) {
                    this.payments[0].amount = parseFloat((val / 100).toFixed(2));
                }
            });
        },

        get subtotal() {
            return this.cart.reduce((s, i) => s + i.price * i.qty, 0);
        },
        get discountAmount() {
            if (this.promoDiscount > 0) return this.promoDiscount;
            if (!this.discountValue || this.discountValue <= 0) return 0;
            if (this.discountType === 'percent') {
                return Math.round(this.subtotal * this.discountValue / 100);
            }
            return Math.min(Math.round(this.discountValue * 100), this.subtotal);
        },
        get taxAmount() {
            if (this.taxInclusive) return 0;
            const taxable = this.subtotal - this.discountAmount;
            return Math.round(taxable * this.taxRate / 100);
        },
        get total() {
            return Math.max(0, this.subtotal - this.discountAmount + this.taxAmount);
        },
        get totalPaid() {
            return Math.round(this.payments.reduce((s, p) => s + (parseFloat(p.amount) || 0), 0) * 100);
        },
        get changeAmount() {
            return Math.max(0, this.totalPaid - this.total);
        },

        searchProducts() {
            if (!this.search.trim()) { this.searchResults = []; return; }
            const q = this.search.toLowerCase();
            this.searchResults = this.allProducts.filter(p =>
                p.name.toLowerCase().includes(q) ||
                (p.sku && p.sku.toLowerCase().includes(q)) ||
                (p.barcode && p.barcode.toLowerCase().includes(q))
            ).slice(0, 12);
        },

        addToCart(product) {
            if (product.current_stock <= 0) {
                alert('This product is out of stock.');
                return;
            }
            const existing = this.cart.find(i => i.id === product.id);
            if (existing) {
                if (existing.qty < product.current_stock) existing.qty++;
                else alert('Maximum available stock: ' + product.current_stock);
            } else {
                this.cart.push({
                    id: product.id,
                    name: product.name,
                    sku: product.sku,
                    price: product.selling_price,
                    cost: product.cost_price,
                    qty: 1,
                    stock: product.current_stock,
                    has_serial: product.has_serial,
                    serial_number: '',
                });
            }
        },

        addPaymentLine() {
            this.payments.push({ method: '{{ $paymentMethods[0] ?? 'cash' }}', amount: 0 });
        },

        async applyPromo() {
            if (!this.promoCode.trim()) return;
            try {
                const res = await fetch('{{ route('sales.apply-promo') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ code: this.promoCode, order_amount: this.subtotal / 100 }),
                });
                const data = await res.json();
                if (data.valid) {
                    this.promoValid = true;
                    this.promoDiscount = data.discount_cents;
                    this.promoMessage = 'Promo applied: -' + this.sym + (data.discount_cents / 100).toFixed(2);
                } else {
                    this.promoValid = false;
                    this.promoDiscount = 0;
                    this.promoMessage = data.message;
                }
            } catch (e) {
                this.promoMessage = 'Failed to apply promo code.';
            }
        },

        holdCart() {
            if (this.cart.length === 0) return;
            this.showHoldModal = true;
        },

        async confirmHold() {
            await fetch('{{ route('sales.hold') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ cart_data: this.cart, name: this.holdName }),
            });
            this.cart = [];
            this.showHoldModal = false;
            this.holdName = '';
            alert('Cart held successfully.');
        },

        submitSale() {
            if (this.cart.length === 0) return;
            if (this.totalPaid < this.total) return;

            const container = document.getElementById('sale-form-fields');
            container.innerHTML = '';

            const add = (name, value) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                container.appendChild(input);
            };

            this.cart.forEach((item, i) => {
                add(`items[${i}][product_id]`, item.id);
                add(`items[${i}][quantity]`, item.qty);
                add(`items[${i}][unit_price]`, (item.price / 100).toFixed(2));
                add(`items[${i}][cost_price]`, (item.cost / 100).toFixed(2));
                add(`items[${i}][tax_amount]`, (this.taxInclusive ? 0 : (item.price * item.qty * this.taxRate / 100 / 100)).toFixed(2));
                add(`items[${i}][discount_amount]`, '0');
                if (item.serial_number) add(`items[${i}][serial_number]`, item.serial_number);
            });

            this.payments.forEach((pmt, i) => {
                add(`payments[${i}][method]`, pmt.method);
                add(`payments[${i}][amount]`, parseFloat(pmt.amount).toFixed(2));
            });

            add('subtotal_amount', (this.subtotal / 100).toFixed(2));
            add('tax_amount', (this.taxAmount / 100).toFixed(2));
            add('discount_amount', (this.discountAmount / 100).toFixed(2));
            add('final_amount', (this.total / 100).toFixed(2));
            add('notes', this.notes);
            if (this.promoCode && this.promoValid) add('promo_code', this.promoCode);
            if (this.customerPhone) add('customer_phone', this.customerPhone);
            if (this.customerEmail) add('customer_email', this.customerEmail);

            document.getElementById('sale-form').submit();
        },
    };
}
</script>
@endsection
