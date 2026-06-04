<?php

namespace App\Http\Controllers\Sales;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\Refund;
use App\Models\Sale;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    public function create(Sale $sale)
    {
        if ($sale->status !== 'completed') {
            return back()->withErrors(['error' => 'Only completed sales can be refunded.']);
        }

        $sale->load(['items.product', 'payments', 'customer']);
        $alreadyRefunded = $sale->refunds()->sum('amount');
        $maxRefundable   = $sale->final_amount - $alreadyRefunded;
        $sym             = Setting::get('currency_symbol', '$');

        return view('sales.refund', compact('sale', 'alreadyRefunded', 'maxRefundable', 'sym'));
    }

    public function store(Request $request, Sale $sale)
    {
        if ($sale->status !== 'completed') {
            return back()->withErrors(['error' => 'Only completed sales can be refunded.']);
        }

        $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,card',
            'reason'         => 'nullable|string|max:500',
            'restock'        => 'boolean',
        ]);

        $amountCents     = Money::toCents($request->amount);
        $alreadyRefunded = $sale->refunds()->sum('amount');
        $maxRefundable   = $sale->final_amount - $alreadyRefunded;

        if ($amountCents > $maxRefundable) {
            return back()->withErrors([
                'amount' => 'Refund amount exceeds maximum refundable: ' . Money::format($maxRefundable),
            ])->withInput();
        }

        DB::transaction(function () use ($request, $sale, $amountCents) {
            $refund = Refund::create([
                'sale_id'        => $sale->id,
                'user_id'        => Auth::id(),
                'amount'         => $amountCents,
                'payment_method' => $request->payment_method,
                'reason'         => $request->reason,
                'restock'        => $request->boolean('restock'),
            ]);

            // Return money to customer (deduct from cash/bank balance)
            if ($request->payment_method === 'cash') {
                $balance = (int) Setting::get('cash_balance', 0);
                Setting::set('cash_balance', $balance - $amountCents, 'integer', 'finance');
            } else {
                $balance = (int) Setting::get('bank_balance', 0);
                Setting::set('bank_balance', $balance - $amountCents, 'integer', 'finance');
            }

            // If restocking — add items back to inventory
            if ($request->boolean('restock')) {
                foreach ($sale->items as $item) {
                    $product  = $item->product;
                    if (!$product) continue;

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
                        'reason'          => 'refund',
                        'reference_type'  => 'Refund',
                        'reference_id'    => $refund->id,
                    ]);
                }
            }

            ActivityLogger::log(
                'sale_refund',
                "Refund of " . Money::format($amountCents) . " on Sale #{$sale->id} via {$request->payment_method}",
                Refund::class,
                $refund->id
            );
        });

        return redirect()->route('sales.show', $sale)->with('success', 'Refund processed successfully.');
    }
}
