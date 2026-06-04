<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Quotation;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::with(['user', 'customer'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sq) use ($q) {
                $sq->where('id', 'like', "%{$q}%")
                   ->orWhere('customer_reference', 'like', "%{$q}%")
                   ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$q}%"));
            });
        }

        $quotations = $query->paginate(20)->withQueryString();

        return view('sales.quotations.index', compact('quotations'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);

        return view('sales.quotations.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'        => 'nullable|integer|exists:customers,id',
            'customer_reference' => 'nullable|string|max:255',
            'cart_data'          => 'required|array|min:1',
            'expires_at'         => 'nullable|date|after:today',
        ]);

        $quotation = Quotation::create([
            'user_id'            => Auth::id(),
            'customer_id'        => $request->customer_id,
            'customer_reference' => $request->customer_reference,
            'cart_data'          => $request->cart_data,
            'status'             => 'open',
            'expires_at'         => $request->expires_at,
        ]);

        ActivityLogger::log('quotation_create', "Quotation #{$quotation->id} created", Quotation::class, $quotation->id);

        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Quotation saved successfully.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['user', 'customer']);

        return view('sales.quotations.show', compact('quotation'));
    }

    public function destroy(Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return back()->withErrors(['error' => 'Cannot delete a converted quotation.']);
        }

        ActivityLogger::log('quotation_delete', "Quotation #{$quotation->id} deleted", Quotation::class, $quotation->id);

        $quotation->delete();

        return redirect()->route('quotations.index')->with('success', 'Quotation deleted.');
    }

    /**
     * Load quotation's cart into session and redirect to POS.
     */
    public function convert(Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return back()->withErrors(['error' => 'This quotation has already been converted to a sale.']);
        }

        if ($quotation->expires_at && $quotation->expires_at->isPast()) {
            return back()->withErrors(['error' => 'This quotation has expired.']);
        }

        session(['restored_cart' => $quotation->cart_data]);

        // Mark as converted — will be linked to sale via session
        $quotation->update(['status' => 'sent']);

        return redirect()->route('sales.create')
            ->with('success', 'Quotation loaded into the POS.')
            ->with('quotation_id', $quotation->id);
    }
}
