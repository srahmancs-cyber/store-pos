<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'creator'])->latest();

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        $purchaseOrders = $query->paginate(20)->withQueryString();
        $suppliers      = Supplier::orderBy('name')->get();

        return view('inventory.purchase-orders.index', compact('purchaseOrders', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products  = Product::where('is_active', true)->orderBy('name')->get();

        return view('inventory.purchase-orders.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'         => 'required|integer|exists:suppliers,id',
            'order_date'          => 'required|date',
            'expected_delivery'   => 'nullable|date|after_or_equal:order_date',
            'notes'               => 'nullable|string|max:1000',
            'items'               => 'required|array|min:1',
            'items.*.product_id'        => 'required|integer|exists:products,id',
            'items.*.ordered_quantity'  => 'required|integer|min:1',
            'items.*.unit_cost'         => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $itemsData   = [];

            foreach ($request->items as $item) {
                $unitCostCents = Money::toCents($item['unit_cost']);
                $lineTotal     = $unitCostCents * $item['ordered_quantity'];
                $totalAmount  += $lineTotal;

                $product = Product::find($item['product_id']);

                $itemsData[] = [
                    'product_id'         => $item['product_id'],
                    'product_name'       => $product->name,
                    'ordered_quantity'   => $item['ordered_quantity'],
                    'received_quantity'  => 0,
                    'unit_cost'          => $unitCostCents,
                ];
            }

            $po = PurchaseOrder::create([
                'supplier_id'       => $request->supplier_id,
                'order_date'        => $request->order_date,
                'expected_delivery' => $request->expected_delivery,
                'status'            => 'pending',
                'total_amount'      => $totalAmount,
                'notes'             => $request->notes,
                'created_by'        => Auth::id(),
            ]);

            foreach ($itemsData as $itemData) {
                $po->items()->create($itemData);
            }

            ActivityLogger::log('purchase_order_create', "Purchase order #{$po->id} created", PurchaseOrder::class, $po->id);
        });

        return redirect()->route('inventory.purchase-orders.index')->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.product', 'creator']);

        return view('inventory.purchase-orders.show', compact('purchaseOrder'));
    }

    public function receiveForm(PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return back()->withErrors(['error' => 'This purchase order cannot be received.']);
        }

        $purchaseOrder->load(['supplier', 'items.product']);

        return view('inventory.purchase-orders.receive', compact('purchaseOrder'));
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return back()->withErrors(['error' => 'This purchase order cannot be received.']);
        }

        $request->validate([
            'items'                          => 'required|array',
            'items.*.id'                     => 'required|integer|exists:purchase_order_items,id',
            'items.*.received_quantity'      => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $purchaseOrder) {
            $allReceived     = true;
            $anyReceived     = false;

            foreach ($request->items as $itemInput) {
                /** @var PurchaseOrderItem $poItem */
                $poItem = $purchaseOrder->items()->findOrFail($itemInput['id']);

                $receivedQty = (int) $itemInput['received_quantity'];

                if ($receivedQty <= 0) {
                    continue;
                }

                $anyReceived = true;

                // Update the PO item received quantity
                $poItem->increment('received_quantity', $receivedQty);
                $poItem->refresh();

                if ($poItem->received_quantity < $poItem->ordered_quantity) {
                    $allReceived = false;
                }

                // Update product stock and recalculate weighted average cost
                $product     = $poItem->product;
                $oldStock    = $product->current_stock;
                $newStock    = $oldStock + $receivedQty;

                // Weighted average cost: (existing_stock * old_cost + received_qty * po_cost) / new_stock
                $newCostPrice = $newStock > 0
                    ? (int) round(($oldStock * $product->cost_price + $receivedQty * $poItem->unit_cost) / $newStock)
                    : $poItem->unit_cost;

                $product->update([
                    'current_stock' => $newStock,
                    'cost_price'    => $newCostPrice,
                ]);

                // Create inventory log
                InventoryLog::create([
                    'product_id'      => $product->id,
                    'user_id'         => Auth::id(),
                    'type'            => 'purchase',
                    'adjustment_type' => 'add',
                    'quantity'        => $receivedQty,
                    'old_quantity'    => $oldStock,
                    'new_quantity'    => $newStock,
                    'reason'          => 'purchase_order',
                    'reference_type'  => 'PurchaseOrder',
                    'reference_id'    => $purchaseOrder->id,
                ]);
            }

            // Determine new PO status
            $purchaseOrder->load('items');
            $allFullyReceived = $purchaseOrder->items->every(fn ($i) => $i->received_quantity >= $i->ordered_quantity);

            $purchaseOrder->update([
                'status' => $allFullyReceived ? 'received' : 'partial',
            ]);

            ActivityLogger::log(
                'purchase_order_receive',
                "Purchase order #{$purchaseOrder->id} items received",
                PurchaseOrder::class,
                $purchaseOrder->id
            );
        });

        return redirect()->route('inventory.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Stock received and inventory updated.');
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending orders can be cancelled.']);
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        ActivityLogger::log('purchase_order_cancel', "Purchase order #{$purchaseOrder->id} cancelled", PurchaseOrder::class, $purchaseOrder->id);

        return redirect()->route('inventory.purchase-orders.index')->with('success', 'Purchase order cancelled.');
    }
}
