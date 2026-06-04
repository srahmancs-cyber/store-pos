@extends('layouts.app')
@section('title', 'New Quotation')
@section('breadcrumb')
    <a href="{{ route('quotations.index') }}" class="text-gray-500 hover:text-gray-900">Quotations</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">New Quotation</span>
@endsection
@section('content')
@php
    $sym = \App\Models\Setting::get('currency_symbol', '$');
    $products = \App\Models\Product::where('is_active', true)->with('category')->orderBy('name')->get();
@endphp

<div class="pt-6 max-w-4xl"
    x-data="{
        cart: [],
        search: '',
        searchResults: [],
        get subtotal() { return this.cart.reduce((s,i) => s + (i.price * i.qty), 0) },
        addToCart(p) {
            const ex = this.cart.find(i => i.id === p.id);
            if (ex) ex.qty++;
            else this.cart.push({ id: p.id, name: p.name, sku: p.sku, price: p.selling_price, cost: p.cost_price, qty: 1 });
            this.search = ''; this.searchResults = [];
        },
        searchProducts() {
            if (!this.search.trim()) { this.searchResults = []; return; }
            const q = this.search.toLowerCase();
            this.searchResults = {{ json_encode($products) }}.filter(p =>
                p.name.toLowerCase().includes(q) || (p.sku && p.sku.toLowerCase().includes(q))
            ).slice(0, 10);
        }
    }">

    <div class="flex items-center justify-between mb-6">
        <h1>New Quotation</h1>
    </div>

    <form method="POST" action="{{ route('quotations.store') }}" id="quotation-form">
        @csrf
        <div id="quotation-fields"></div>

        <div class="grid grid-cols-5 gap-5">
            <div class="col-span-3 space-y-4">
                {{-- Search --}}
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" x-model="search" @input.debounce.150ms="searchProducts()"
                        placeholder="Search products…" class="form-input pl-9">
                    <div x-show="searchResults.length > 0" x-cloak
                        class="absolute z-50 top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="p in searchResults" :key="p.id">
                            <div @click="addToCart(p)"
                                class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0">
                                <div>
                                    <p class="font-medium text-sm" x-text="p.name"></p>
                                    <p class="text-xs text-gray-400" x-text="'SKU: ' + p.sku"></p>
                                </div>
                                <span class="text-sm font-medium" x-text="'{{ $sym }}' + (p.selling_price/100).toFixed(2)"></span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Cart --}}
                <div class="card">
                    <table class="table">
                        <thead><tr>
                            <th>Product</th>
                            <th class="w-24">Qty</th>
                            <th class="w-28">Unit Price</th>
                            <th class="w-28">Total</th>
                            <th class="w-8"></th>
                        </tr></thead>
                        <tbody>
                            <template x-if="cart.length === 0">
                                <tr><td colspan="5" class="text-center text-gray-400 py-8">Add products to the quotation</td></tr>
                            </template>
                            <template x-for="(item, i) in cart" :key="i">
                                <tr>
                                    <td class="font-medium text-sm" x-text="item.name"></td>
                                    <td><input type="number" x-model.number="item.qty" min="1" class="form-input w-20 text-center"></td>
                                    <td x-text="'{{ $sym }}' + (item.price/100).toFixed(2)"></td>
                                    <td class="font-medium" x-text="'{{ $sym }}' + (item.price * item.qty / 100).toFixed(2)"></td>
                                    <td><button type="button" @click="cart.splice(i,1)" class="text-gray-300 hover:text-red-500"><i data-lucide="x" class="w-4 h-4"></i></button></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div class="px-4 py-3 border-t border-gray-100 flex justify-end">
                        <div class="text-sm font-semibold">
                            Subtotal: <span x-text="'{{ $sym }}' + (subtotal/100).toFixed(2)"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Panel --}}
            <div class="col-span-2 space-y-4">
                <div class="card">
                    <div class="card-header"><h3>Quotation Details</h3></div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="form-label">Customer Reference</label>
                            <input type="text" name="customer_reference" class="form-input"
                                placeholder="e.g. Customer name / project">
                        </div>
                        <div>
                            <label class="form-label">Customer (optional)</label>
                            <select name="customer_id" class="form-select">
                                <option value="">Walk-in</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expires_at" class="form-input"
                                value="{{ now()->addDays(30)->toDateString() }}">
                        </div>
                    </div>
                </div>

                <button type="button" @click="submitQuotation()"
                    :disabled="cart.length === 0"
                    :class="cart.length > 0 ? 'btn-primary' : 'btn-secondary opacity-50 cursor-not-allowed'"
                    class="w-full py-3 justify-center btn-primary">
                    Save Quotation
                </button>
                <a href="{{ route('quotations.index') }}" class="btn-secondary w-full justify-center">Cancel</a>
            </div>
        </div>
    </form>
</div>

<script>
function submitQuotation() {
    const cart = Alpine.evaluate(document.querySelector('[x-data]'), 'cart');
    if (!cart.length) return;
    const container = document.getElementById('quotation-fields');
    container.innerHTML = '';
    cart.forEach((item, i) => {
        ['id','name','price','cost','qty'].forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `cart_data[${i}][${key === 'id' ? 'id' : key}]`;
            input.value = item[key === 'id' ? 'id' : key];
            container.appendChild(input);
        });
    });
    document.getElementById('quotation-form').submit();
}
</script>
@endsection
