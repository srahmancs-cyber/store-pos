<?php

namespace App\Http\Controllers\Sales;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\Setting;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function index()
    {
        $codes = PromoCode::latest()->paginate(20);
        $sym   = Setting::get('currency_symbol', '$');

        return view('sales.promo-codes.index', compact('codes', 'sym'));
    }

    public function create()
    {
        return view('sales.promo-codes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'             => 'required|string|max:50|unique:promo_codes,code|alpha_dash',
            'discount_type'    => 'required|in:percentage,fixed',
            'discount_value'   => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses'         => 'nullable|integer|min:1',
            'starts_at'        => 'nullable|date',
            'expires_at'       => 'nullable|date|after_or_equal:starts_at',
            'is_active'        => 'boolean',
        ]);

        // Validate percentage cap
        if ($request->discount_type === 'percentage' && $request->discount_value > 100) {
            return back()->withErrors(['discount_value' => 'Percentage discount cannot exceed 100%'])->withInput();
        }

        // Store percentage as-is (e.g. 10 = 10%), fixed amount in cents
        $discountValue = $request->discount_type === 'percentage'
            ? (int) round($request->discount_value * 100) // stored as basis points: 10% = 1000
            : Money::toCents($request->discount_value);

        PromoCode::create([
            'code'             => strtoupper(trim($request->code)),
            'discount_type'    => $request->discount_type,
            'discount_value'   => $discountValue,
            'min_order_amount' => $request->filled('min_order_amount') ? Money::toCents($request->min_order_amount) : 0,
            'max_uses'         => $request->max_uses ?: null,
            'starts_at'        => $request->starts_at,
            'expires_at'       => $request->expires_at,
            'is_active'        => $request->boolean('is_active', true),
        ]);

        return redirect()->route('promo-codes.index')->with('success', 'Promo code created.');
    }

    public function edit(PromoCode $promoCode)
    {
        $sym = Setting::get('currency_symbol', '$');

        return view('sales.promo-codes.edit', compact('promoCode', 'sym'));
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        $request->validate([
            'discount_type'    => 'required|in:percentage,fixed',
            'discount_value'   => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses'         => 'nullable|integer|min:1',
            'starts_at'        => 'nullable|date',
            'expires_at'       => 'nullable|date',
            'is_active'        => 'boolean',
        ]);

        $discountValue = $request->discount_type === 'percentage'
            ? (int) round($request->discount_value * 100)
            : Money::toCents($request->discount_value);

        $promoCode->update([
            'discount_type'    => $request->discount_type,
            'discount_value'   => $discountValue,
            'min_order_amount' => $request->filled('min_order_amount') ? Money::toCents($request->min_order_amount) : 0,
            'max_uses'         => $request->max_uses ?: null,
            'starts_at'        => $request->starts_at,
            'expires_at'       => $request->expires_at,
            'is_active'        => $request->boolean('is_active'),
        ]);

        return redirect()->route('promo-codes.index')->with('success', 'Promo code updated.');
    }

    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();

        return redirect()->route('promo-codes.index')->with('success', 'Promo code deleted.');
    }
}
