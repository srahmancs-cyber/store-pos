<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'supplier']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('sku', 'like', "%{$q}%")
                   ->orWhere('barcode', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('low_stock') && $request->low_stock) {
            $query->whereColumn('current_stock', '<=', 'reorder_point');
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $products   = $query->orderBy('name')->paginate(25)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();

        return view('inventory.products.index', compact('products', 'categories', 'suppliers'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('inventory.products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'sku'           => 'required|string|max:100|unique:products,sku',
            'barcode'       => 'nullable|string|max:100|unique:products,barcode',
            'category_id'   => 'nullable|integer|exists:categories,id',
            'supplier_id'   => 'nullable|integer|exists:suppliers,id',
            'selling_price' => 'required|numeric|min:0',
            'cost_price'    => 'required|numeric|min:0',
            'current_stock' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
            'image'         => 'nullable|image|max:2048',
            'is_active'     => 'boolean',
            'has_serial'    => 'boolean',
        ]);

        $data = $request->only('name', 'sku', 'barcode', 'category_id', 'supplier_id', 'current_stock', 'reorder_point', 'has_serial');
        $data['selling_price']        = Money::toCents($request->selling_price);
        $data['cost_price']            = Money::toCents($request->cost_price);
        $data['is_active']             = $request->boolean('is_active', true);
        $data['has_serial']            = $request->boolean('has_serial', false);
        $data['is_consignment']        = $request->boolean('is_consignment', false);
        $data['consignment_vendor_id'] = $data['is_consignment'] ? $request->consignment_vendor_id : null;
        $data['consignment_rate']      = $data['is_consignment'] && $request->filled('consignment_rate') ? $request->consignment_rate : null;
        $data['consignment_basis']     = $data['is_consignment'] && $request->filled('consignment_basis') ? $request->consignment_basis : null;
        $data['investor_id']           = $request->filled('investor_id') ? $request->investor_id : null;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($data, $request) {
            $product = Product::create($data);

            // Log initial stock as adjustment
            if ($product->current_stock > 0) {
                InventoryLog::create([
                    'product_id'      => $product->id,
                    'user_id'         => Auth::id(),
                    'type'            => 'adjustment',
                    'adjustment_type' => 'add',
                    'quantity'        => $product->current_stock,
                    'old_quantity'    => 0,
                    'new_quantity'    => $product->current_stock,
                    'reason'          => 'initial_stock',
                    'reference_type'  => 'Product',
                    'reference_id'    => $product->id,
                ]);
            }

            ActivityLogger::log('product_create', "Product '{$product->name}' created (SKU: {$product->sku})", Product::class, $product->id);
        });

        return redirect()->route('inventory.products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'supplier']);
        $inventoryLogs = $product->inventoryLogs()->latest('created_at')->take(20)->get();

        return view('inventory.products.show', compact('product', 'inventoryLogs'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('inventory.products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'sku'           => 'required|string|max:100|unique:products,sku,' . $product->id,
            'barcode'       => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'category_id'   => 'nullable|integer|exists:categories,id',
            'supplier_id'   => 'nullable|integer|exists:suppliers,id',
            'selling_price' => 'required|numeric|min:0',
            'cost_price'    => 'required|numeric|min:0',
            'reorder_point' => 'required|integer|min:0',
            'image'         => 'nullable|image|max:2048',
            'is_active'     => 'boolean',
            'has_serial'    => 'boolean',
        ]);

        $data = $request->only('name', 'sku', 'barcode', 'category_id', 'supplier_id', 'reorder_point');
        $data['selling_price']         = Money::toCents($request->selling_price);
        $data['cost_price']            = Money::toCents($request->cost_price);
        $data['is_active']             = $request->boolean('is_active');
        $data['has_serial']            = $request->boolean('has_serial');
        $data['is_consignment']        = $request->boolean('is_consignment', false);
        $data['consignment_vendor_id'] = $data['is_consignment'] ? $request->consignment_vendor_id : null;
        $data['consignment_rate']      = $data['is_consignment'] && $request->filled('consignment_rate') ? $request->consignment_rate : null;
        $data['consignment_basis']     = $data['is_consignment'] && $request->filled('consignment_basis') ? $request->consignment_basis : null;
        $data['investor_id']           = $request->filled('investor_id') ? $request->investor_id : null;

        if ($request->hasFile('image')) {
            // Remove old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        ActivityLogger::log('product_update', "Product '{$product->name}' updated", Product::class, $product->id);

        return redirect()->route('inventory.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->saleItems()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete product that has sales history.']);
        }

        if ($product->purchaseOrderItems()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete product that has purchase order history.']);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        ActivityLogger::log('product_delete', "Product '{$product->name}' deleted", Product::class, $product->id);

        $product->delete();

        return redirect()->route('inventory.products.index')->with('success', 'Product deleted successfully.');
    }
}
