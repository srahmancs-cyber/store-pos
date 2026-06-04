<?php

namespace App\Http\Controllers\Sales;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\HeldCart;
use App\Models\InventoryLog;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    // -------------------------------------------------------------------------
    // POS Screen
    // -------------------------------------------------------------------------

    public function create()
    {
        $products       = Product::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        $paymentMethods = Setting::get('payment_methods', ['cash', 'card']);
        $taxRate        = Setting::get('tax_rate', 0);
        $taxName        = Setting::get('tax_name', 'Tax');
        $taxInclusive   = Setting::get('tax_inclusive', false);
        $currencySymbol = Setting::get('currency_symbol', '$');
        $customers      = Customer::orderBy('name')->get(['id', 'name', 'phone']);

        return view('sales.create', compact(
            'products', 'paymentMethods', 'taxRate', 'taxName',
            'taxInclusive', 'currencySymbol', 'customers'
        ));
    }

    // -------------------------------------------------------------------------
    // Checkout
    // -------------------------------------------------------------------------

    public function store(Request $request)
    {
        $request->validate([
            'items'                        => 'required|array|min:1',
            'items.*.product_id'           => 'required|integer|exists:products,id',
            'items.*.quantity'             => 'required|integer|min:1',
            'items.*.unit_price'           => 'required|numeric|min:0',
            'items.*.cost_price'           => 'required|numeric|min:0',
            'items.*.discount_amount'      => 'nullable|numeric|min:0',
            'items.*.tax_amount'           => 'nullable|numeric|min:0',
            'subtotal_amount'              => 'required|numeric|min:0',
            'tax_amount'                   => 'required|numeric|min:0',
            'discount_amount'              => 'nullable|numeric|min:0',
            'final_amount'                 => 'required|numeric|min:0',
            'payments'                     => 'required|array|min:1',
            'payments.*.method'            => 'required|string',
            'payments.*.amount'            => 'required|numeric|min:0',
            'customer_id'                  => 'nullable|integer|exists:customers,id',
            'notes'                        => 'nullable|string|max:500',
            'promo_code'                   => 'nullable|string|max:50',
        ]);

        $finalAmountCents = Money::toCents($request->final_amount);

        // Verify payment totals cover the final amount
        $totalPaid = array_sum(array_column($request->payments, 'amount'));
        if (Money::toCents($totalPaid) < $finalAmountCents) {
            return back()
                ->withErrors(['payments' => 'Total payments must be greater than or equal to the final amount.'])
                ->withInput();
        }

        $sale = DB::transaction(function () use ($request, $finalAmountCents) {
            // Verify stock for all items first
            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                if ($product->current_stock < $item['quantity']) {
                    throw new \InvalidArgumentException(
                        "Insufficient stock for '{$product->name}'. Available: {$product->current_stock}, requested: {$item['quantity']}."
                    );
                }
            }

            // Create the sale
            $sale = Sale::create([
                'user_id'         => Auth::id(),
                'customer_id'     => $request->customer_id,
                'subtotal_amount' => Money::toCents($request->subtotal_amount),
                'tax_amount'      => Money::toCents($request->tax_amount),
                'discount_amount' => Money::toCents($request->discount_amount ?? 0),
                'final_amount'    => $finalAmountCents,
                'status'          => 'completed',
                'notes'           => $request->notes,
            ]);

            // Create sale items and update stock
            foreach ($request->items as $item) {
                $product  = Product::lockForUpdate()->find($item['product_id']);
                $oldStock = $product->current_stock;
                $newStock = $oldStock - $item['quantity'];

                $sale->items()->create([
                    'product_id'      => $product->id,
                    'product_name'    => $product->name,
                    'unit_price'      => Money::toCents($item['unit_price']),
                    'cost_price'      => Money::toCents($item['cost_price']),
                    'quantity'        => $item['quantity'],
                    'discount_amount' => Money::toCents($item['discount_amount'] ?? 0),
                    'tax_amount'      => Money::toCents($item['tax_amount'] ?? 0),
                    'total'           => (Money::toCents($item['unit_price']) * $item['quantity'])
                                        - Money::toCents($item['discount_amount'] ?? 0)
                                        + Money::toCents($item['tax_amount'] ?? 0),
                    'serial_number'   => $item['serial_number'] ?? null,
                ]);

                $product->update(['current_stock' => $newStock]);

                InventoryLog::create([
                    'product_id'      => $product->id,
                    'user_id'         => Auth::id(),
                    'type'            => 'sale',
                    'adjustment_type' => 'remove',
                    'quantity'        => $item['quantity'],
                    'old_quantity'    => $oldStock,
                    'new_quantity'    => $newStock,
                    'reason'          => 'sale',
                    'reference_type'  => 'Sale',
                    'reference_id'    => $sale->id,
                ]);
            }

            // Create payment records and update balances
            foreach ($request->payments as $payment) {
                if ((float) $payment['amount'] <= 0) {
                    continue;
                }

                $paymentCents = Money::toCents($payment['amount']);

                Payment::create([
                    'sale_id'          => $sale->id,
                    'amount'           => $paymentCents,
                    'payment_method'   => $payment['method'],
                    'reference_number' => $payment['reference_number'] ?? null,
                ]);

                // Update cash or bank balance when payment received
                if ($payment['method'] === 'cash') {
                    $balance = (int) \App\Models\Setting::get('cash_balance', 0);
                    \App\Models\Setting::set('cash_balance', $balance + $paymentCents, 'integer', 'finance');
                } elseif ($payment['method'] === 'bank' || $payment['method'] === 'card') {
                    $balance = (int) \App\Models\Setting::get('bank_balance', 0);
                    \App\Models\Setting::set('bank_balance', $balance + $paymentCents, 'integer', 'finance');
                }
            }

            // Increment promo code usage
            if ($request->filled('promo_code')) {
                PromoCode::where('code', $request->promo_code)->increment('used_count');
            }

            ActivityLogger::log('sale_create', "Sale #{$sale->id} completed — " . Money::format($finalAmountCents), Sale::class, $sale->id);

            return $sale;
        });

        return redirect()->route('sales.receipt', $sale)->with('success', 'Sale completed successfully.');
    }

    // -------------------------------------------------------------------------
    // Sales List
    // -------------------------------------------------------------------------

    public function index(Request $request)
    {
        $query = Sale::with(['user', 'customer'])->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sq) use ($q) {
                $sq->where('id', 'like', "%{$q}%")
                   ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$q}%"));
            });
        }

        $sales          = $query->paginate(25)->withQueryString();
        $currencySymbol = Setting::get('currency_symbol', '$');

        return view('sales.index', compact('sales', 'currencySymbol'));
    }

    // -------------------------------------------------------------------------
    // Sale Detail
    // -------------------------------------------------------------------------

    public function show(Sale $sale)
    {
        $sale->load(['items.product', 'payments', 'user', 'customer', 'voidedByUser']);
        $currencySymbol = Setting::get('currency_symbol', '$');

        return view('sales.show', compact('sale', 'currencySymbol'));
    }

    // -------------------------------------------------------------------------
    // Receipt
    // -------------------------------------------------------------------------

    public function receipt(Sale $sale)
    {
        $sale->load(['items.product', 'payments', 'user', 'customer']);

        $shopName       = Setting::get('shop_name', 'Store POS');
        $shopAddress    = Setting::get('shop_address', '');
        $shopPhone      = Setting::get('shop_phone', '');
        $receiptHeader  = Setting::get('receipt_header', '');
        $receiptFooter  = Setting::get('receipt_footer', '');
        $currencySymbol = Setting::get('currency_symbol', '$');
        $taxName        = Setting::get('tax_name', 'Tax');

        return view('sales.receipt', compact(
            'sale', 'shopName', 'shopAddress', 'shopPhone',
            'receiptHeader', 'receiptFooter', 'currencySymbol', 'taxName'
        ));
    }

    // -------------------------------------------------------------------------
    // Void Sale
    // -------------------------------------------------------------------------

    public function void(Sale $sale)
    {
        if ($sale->status !== 'completed') {
            return back()->withErrors(['error' => 'Only completed sales can be voided.']);
        }

        DB::transaction(function () use ($sale) {
            // Restore stock for each item
            foreach ($sale->items as $item) {
                $product  = Product::lockForUpdate()->find($item->product_id);
                if (!$product) {
                    continue;
                }

                $oldStock = $product->current_stock;
                $newStock = $oldStock + $item->quantity;

                $product->update(['current_stock' => $newStock]);

                InventoryLog::create([
                    'product_id'      => $product->id,
                    'user_id'         => Auth::id(),
                    'type'            => 'adjustment',
                    'adjustment_type' => 'add',
                    'quantity'        => $item->quantity,
                    'old_quantity'    => $oldStock,
                    'new_quantity'    => $newStock,
                    'reason'          => 'sale_void',
                    'reference_type'  => 'Sale',
                    'reference_id'    => $sale->id,
                ]);
            }

            $sale->update([
                'status'    => 'voided',
                'voided_by' => Auth::id(),
                'voided_at' => now(),
            ]);

            // Reverse the payment balances
            $sale->load('payments');
            foreach ($sale->payments as $payment) {
                if ($payment->payment_method === 'cash') {
                    $balance = (int) \App\Models\Setting::get('cash_balance', 0);
                    \App\Models\Setting::set('cash_balance', max(0, $balance - $payment->amount), 'integer', 'finance');
                } elseif (in_array($payment->payment_method, ['bank', 'card'])) {
                    $balance = (int) \App\Models\Setting::get('bank_balance', 0);
                    \App\Models\Setting::set('bank_balance', max(0, $balance - $payment->amount), 'integer', 'finance');
                }
            }

            ActivityLogger::log('sale_void', "Sale #{$sale->id} voided", Sale::class, $sale->id);
        });

        return redirect()->route('sales.index')->with('success', "Sale #{$sale->id} has been voided and stock restored.");
    }

    // -------------------------------------------------------------------------
    // AJAX: Search Products
    // -------------------------------------------------------------------------

    public function searchProduct(Request $request)
    {
        $q = $request->input('q', '');

        $products = Product::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%")
                      ->orWhere('barcode', 'like', "%{$q}%");
            })
            ->with('category')
            ->take(15)
            ->get()
            ->map(fn ($p) => [
                'id'            => $p->id,
                'name'          => $p->name,
                'sku'           => $p->sku,
                'barcode'       => $p->barcode,
                'selling_price' => $p->selling_price,
                'cost_price'    => $p->cost_price,
                'current_stock' => $p->current_stock,
                'category'      => $p->category?->name,
                'has_serial'    => $p->has_serial,
            ]);

        return response()->json($products);
    }

    // -------------------------------------------------------------------------
    // AJAX: Apply Promo Code
    // -------------------------------------------------------------------------

    public function applyPromo(Request $request)
    {
        $request->validate([
            'code'         => 'required|string|max:50',
            'order_amount' => 'required|numeric|min:0',
        ]);

        $promo = PromoCode::where('code', strtoupper(trim($request->code)))->first();

        if (!$promo || !$promo->isValid()) {
            return response()->json(['valid' => false, 'message' => 'Promo code is invalid or expired.'], 422);
        }

        $orderAmountCents = Money::toCents($request->order_amount);

        if ($promo->min_order_amount && $orderAmountCents < $promo->min_order_amount) {
            $minFormatted = Money::format($promo->min_order_amount);
            return response()->json(['valid' => false, 'message' => "Minimum order amount is {$minFormatted}."], 422);
        }

        $discountCents = 0;
        if ($promo->discount_type === 'percentage') {
            $discountCents = (int) round($orderAmountCents * $promo->discount_value / 10000);
        } else {
            // fixed amount (stored in cents)
            $discountCents = min($promo->discount_value, $orderAmountCents);
        }

        return response()->json([
            'valid'          => true,
            'discount_type'  => $promo->discount_type,
            'discount_value' => $promo->discount_value,
            'discount_cents' => $discountCents,
            'code'           => $promo->code,
        ]);
    }

    // -------------------------------------------------------------------------
    // Hold Cart
    // -------------------------------------------------------------------------

    public function hold(Request $request)
    {
        $request->validate([
            'cart_data' => 'required|array',
            'name'      => 'nullable|string|max:100',
        ]);

        HeldCart::create([
            'user_id'   => Auth::id(),
            'name'      => $request->name ?? 'Hold ' . now()->format('H:i'),
            'cart_data' => $request->cart_data,
        ]);

        return response()->json(['success' => true, 'message' => 'Cart held successfully.']);
    }

    public function heldCarts()
    {
        $heldCarts = HeldCart::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('sales.held-carts', compact('heldCarts'));
    }

    public function restoreCart(int $id)
    {
        $heldCart = HeldCart::where('user_id', Auth::id())->findOrFail($id);

        $cartData = $heldCart->cart_data;

        $heldCart->delete();

        // Pass cart data to the POS via session
        session(['restored_cart' => $cartData]);

        return redirect()->route('sales.create')->with('success', 'Cart restored.');
    }
}
