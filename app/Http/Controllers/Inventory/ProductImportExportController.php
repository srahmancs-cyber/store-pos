<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Supplier;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductImportExportController extends Controller
{
    // -------------------------------------------------------------------------
    // CSV Column Definitions — single source of truth
    // -------------------------------------------------------------------------

    private const HEADERS = [
        'name',
        'sku',
        'barcode',
        'category',
        'supplier',
        'cost_price',
        'selling_price',
        'current_stock',
        'reorder_point',
        'is_active',
        'has_serial',
    ];

    // -------------------------------------------------------------------------
    // Export — download all products as CSV
    // -------------------------------------------------------------------------

    public function export(): StreamedResponse
    {
        $products = Product::with(['category', 'supplier'])
            ->orderBy('name')
            ->get();

        $filename = 'products-export-' . now()->format('Y-m-d') . '.csv';

        ActivityLogger::log('product_export', "Exported {$products->count()} products to CSV");

        return response()->stream(function () use ($products) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM so Excel opens it correctly
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Headers
            fputcsv($handle, self::HEADERS);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->name,
                    $product->sku,
                    $product->barcode ?? '',
                    $product->category?->name ?? '',
                    $product->supplier?->name ?? '',
                    number_format($product->cost_price / 100, 2, '.', ''),
                    number_format($product->selling_price / 100, 2, '.', ''),
                    $product->current_stock,
                    $product->reorder_point,
                    $product->is_active ? '1' : '0',
                    $product->has_serial ? '1' : '0',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
        ]);
    }

    // -------------------------------------------------------------------------
    // Template — download blank CSV with example row
    // -------------------------------------------------------------------------

    public function template(): StreamedResponse
    {
        $sym = Setting::get('currency_symbol', '$');

        return response()->stream(function () use ($sym) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, self::HEADERS);

            // Example row with hints
            fputcsv($handle, [
                'Example T-Shirt',  // name
                'TSH-001',          // sku (must be unique)
                '1234567890123',    // barcode (optional)
                'Clothing',         // category name (auto-created if missing)
                'Main Supplier',    // supplier name (must exist)
                '10.00',            // cost_price (decimal, e.g. 10.00)
                '19.99',            // selling_price (decimal)
                '50',               // current_stock
                '5',                // reorder_point
                '1',                // is_active (1=yes, 0=no)
                '0',                // has_serial (1=yes, 0=no)
            ]);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="products-import-template.csv"',
        ]);
    }

    // -------------------------------------------------------------------------
    // Import — show the import form
    // -------------------------------------------------------------------------

    public function showImport()
    {
        return view('inventory.products.import');
    }

    // -------------------------------------------------------------------------
    // Import — process CSV with validation, return preview
    // -------------------------------------------------------------------------

    public function previewImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120', // max 5MB
        ]);

        $file    = $request->file('csv_file');
        $handle  = fopen($file->getRealPath(), 'r');
        $headers = null;
        $rows    = [];
        $errors  = [];
        $lineNum = 0;

        // Read the file, skip UTF-8 BOM if present
        while (($row = fgetcsv($handle)) !== false) {
            $lineNum++;

            // Skip BOM on first cell of first row
            if ($lineNum === 1) {
                $row[0] = ltrim($row[0], "\xEF\xBB\xBF");
                $headers = array_map('strtolower', array_map('trim', $row));
                continue;
            }

            // Skip blank rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Map columns
            $mapped = [];
            foreach (self::HEADERS as $i => $col) {
                $mapped[$col] = isset($headers) ? ($row[array_search($col, $headers) ?: $i] ?? '') : ($row[$i] ?? '');
            }

            $rowErrors = $this->validateRow($mapped, $lineNum);

            $rows[] = [
                'line'    => $lineNum,
                'data'    => $mapped,
                'errors'  => $rowErrors,
                'action'  => Product::where('sku', trim($mapped['sku']))->exists() ? 'update' : 'create',
            ];

            if (!empty($rowErrors)) {
                $errors[$lineNum] = $rowErrors;
            }
        }

        fclose($handle);

        if (empty($rows)) {
            return back()->withErrors(['csv_file' => 'The CSV file is empty or has no data rows.']);
        }

        // Store validated rows in session for the confirm step
        session(['import_rows' => $rows, 'import_errors' => $errors]);

        $sym = Setting::get('currency_symbol', '$');

        return view('inventory.products.import-preview', compact('rows', 'errors', 'sym'));
    }

    // -------------------------------------------------------------------------
    // Import — commit confirmed rows
    // -------------------------------------------------------------------------

    public function commitImport(Request $request)
    {
        $rows = session('import_rows', []);

        if (empty($rows)) {
            return redirect()->route('inventory.products.import')
                ->withErrors(['error' => 'Import session expired. Please upload the file again.']);
        }

        // Only import rows without errors
        $validRows = array_filter($rows, fn ($r) => empty($r['errors']));

        if (empty($validRows)) {
            return redirect()->route('inventory.products.import')
                ->withErrors(['error' => 'No valid rows to import.']);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($validRows, &$created, &$updated, &$skipped) {
            foreach ($validRows as $row) {
                $data = $row['data'];

                // Resolve or auto-create category
                $categoryId = null;
                if (!empty($data['category'])) {
                    $category   = Category::firstOrCreate(['name' => trim($data['category'])]);
                    $categoryId = $category->id;
                }

                // Resolve supplier (must exist if provided)
                $supplierId = null;
                if (!empty($data['supplier'])) {
                    $supplier   = Supplier::where('name', trim($data['supplier']))->first();
                    $supplierId = $supplier?->id;
                }

                $costPrice    = Money::toCents((float) $data['cost_price']);
                $sellingPrice = Money::toCents((float) $data['selling_price']);
                $stock        = max(0, (int) $data['current_stock']);
                $reorder      = max(0, (int) $data['reorder_point']);
                $isActive     = in_array(trim($data['is_active']), ['1', 'true', 'yes'], true);
                $hasSerial    = in_array(trim($data['has_serial']), ['1', 'true', 'yes'], true);
                $sku          = trim($data['sku']);

                $existing = Product::where('sku', $sku)->first();

                if ($existing) {
                    // Update — never overwrite stock (use stock adjustments for that)
                    $existing->update([
                        'name'          => trim($data['name']),
                        'barcode'       => $data['barcode'] ?: null,
                        'category_id'   => $categoryId,
                        'supplier_id'   => $supplierId,
                        'cost_price'    => $costPrice,
                        'selling_price' => $sellingPrice,
                        'reorder_point' => $reorder,
                        'is_active'     => $isActive,
                        'has_serial'    => $hasSerial,
                    ]);

                    ActivityLogger::log(
                        'product_import_update',
                        "Product '{$existing->name}' (SKU: {$sku}) updated via CSV import",
                        Product::class,
                        $existing->id
                    );

                    $updated++;
                } else {
                    // Create new product
                    $product = Product::create([
                        'name'          => trim($data['name']),
                        'sku'           => $sku,
                        'barcode'       => $data['barcode'] ?: null,
                        'category_id'   => $categoryId,
                        'supplier_id'   => $supplierId,
                        'cost_price'    => $costPrice,
                        'selling_price' => $sellingPrice,
                        'current_stock' => $stock,
                        'reorder_point' => $reorder,
                        'is_active'     => $isActive,
                        'has_serial'    => $hasSerial,
                    ]);

                    // Log initial stock
                    if ($stock > 0) {
                        InventoryLog::create([
                            'product_id'      => $product->id,
                            'user_id'         => Auth::id(),
                            'type'            => 'adjustment',
                            'adjustment_type' => 'add',
                            'quantity'        => $stock,
                            'old_quantity'    => 0,
                            'new_quantity'    => $stock,
                            'reason'          => 'csv_import',
                            'reference_type'  => 'Product',
                            'reference_id'    => $product->id,
                        ]);
                    }

                    ActivityLogger::log(
                        'product_import_create',
                        "Product '{$product->name}' (SKU: {$sku}) created via CSV import",
                        Product::class,
                        $product->id
                    );

                    $created++;
                }
            }
        });

        // Clear import session
        session()->forget(['import_rows', 'import_errors']);

        $message = "Import complete: {$created} created, {$updated} updated.";
        if ($skipped > 0) {
            $message .= " {$skipped} skipped.";
        }

        return redirect()->route('inventory.products.index')->with('success', $message);
    }

    // -------------------------------------------------------------------------
    // Row-level validation
    // -------------------------------------------------------------------------

    private function validateRow(array $data, int $line): array
    {
        $errors = [];

        // name
        if (empty(trim($data['name'] ?? ''))) {
            $errors[] = 'Name is required.';
        }

        // sku
        $sku = trim($data['sku'] ?? '');
        if (empty($sku)) {
            $errors[] = 'SKU is required.';
        }

        // cost_price
        $cost = $data['cost_price'] ?? '';
        if (!is_numeric($cost) || (float) $cost < 0) {
            $errors[] = "Cost price must be a non-negative number (got: '{$cost}').";
        }

        // selling_price
        $sell = $data['selling_price'] ?? '';
        if (!is_numeric($sell) || (float) $sell < 0) {
            $errors[] = "Selling price must be a non-negative number (got: '{$sell}').";
        }

        // current_stock
        $stock = $data['current_stock'] ?? '';
        if (!ctype_digit((string) intval($stock)) && !is_numeric($stock)) {
            $errors[] = "Stock must be a whole number (got: '{$stock}').";
        }

        // reorder_point
        $reorder = $data['reorder_point'] ?? '';
        if (!is_numeric($reorder) || (int) $reorder < 0) {
            $errors[] = "Reorder point must be a non-negative number (got: '{$reorder}').";
        }

        // Supplier existence check (if provided)
        $supplierName = trim($data['supplier'] ?? '');
        if (!empty($supplierName)) {
            if (!Supplier::where('name', $supplierName)->exists()) {
                $errors[] = "Supplier '{$supplierName}' not found. Add it in Inventory → Suppliers first.";
            }
        }

        return $errors;
    }
}
