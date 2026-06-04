<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryLog::with(['product', 'user'])
            ->where('type', 'adjustment')
            ->latest('created_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $adjustments = $query->paginate(30)->withQueryString();
        $products    = Product::orderBy('name')->get(['id', 'name', 'sku']);

        return view('inventory.adjustments.index', compact('adjustments', 'products'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'current_stock']);

        return view('inventory.adjustments.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id'      => 'required|integer|exists:products,id',
            'adjustment_type' => 'required|in:add,remove',
            'quantity'        => 'required|integer|min:1|max:999999',
            'reason'          => 'required|in:damaged,lost,recount',
            'notes'           => 'nullable|string|max:500',
        ]);

        // Check stock won't go negative before opening transaction
        if ($request->adjustment_type === 'remove') {
            $currentStock = Product::where('id', $request->product_id)->value('current_stock');
            if ($currentStock < $request->quantity) {
                return back()
                    ->withErrors(['quantity' => "Cannot remove {$request->quantity} units — only {$currentStock} in stock."])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($request) {
            $product  = Product::lockForUpdate()->findOrFail($request->product_id);
            $oldStock = $product->current_stock;

            if ($request->adjustment_type === 'remove') {
                if ($product->current_stock < $request->quantity) {
                    throw new \InvalidArgumentException(
                        "Cannot remove {$request->quantity} units — only {$product->current_stock} in stock."
                    );
                }
                $newStock = $oldStock - $request->quantity;
            } else {
                $newStock = $oldStock + $request->quantity;
            }

            $product->update(['current_stock' => $newStock]);

            InventoryLog::create([
                'product_id'      => $product->id,
                'user_id'         => Auth::id(),
                'type'            => 'adjustment',
                'adjustment_type' => $request->adjustment_type,
                'quantity'        => $request->quantity,
                'old_quantity'    => $oldStock,
                'new_quantity'    => $newStock,
                'reason'          => $request->reason,
                'reference_type'  => null,
                'reference_id'    => null,
            ]);

            ActivityLogger::log(
                'stock_adjustment',
                "Stock {$request->adjustment_type} for '{$product->name}': {$request->quantity} units ({$request->reason}). " .
                "Stock: {$oldStock} → {$newStock}",
                Product::class,
                $product->id
            );
        });

        return redirect()->route('inventory.adjustments.index')->with('success', 'Stock adjustment recorded.');
    }
}
