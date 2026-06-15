<?php

namespace App\Http\Controllers\Consignment;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\ConsignmentPayout;
use App\Models\ConsignmentVendor;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConsignmentController extends Controller
{
    // ─── Vendor CRUD ──────────────────────────────────────────────────────────

    public function index()
    {
        $vendors = ConsignmentVendor::withCount('products')
            ->orderBy('name')
            ->paginate(20);

        return view('consignment.index', compact('vendors'));
    }

    public function create()
    {
        return view('consignment.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                    => 'required|string|max:255',
            'contact_person'          => 'nullable|string|max:255',
            'phone'                   => 'nullable|string|max:30',
            'email'                   => 'nullable|email|max:255',
            'address'                 => 'nullable|string|max:500',
            'default_commission_rate' => 'required|numeric|min:0|max:100',
            'commission_basis'        => 'required|in:sale_price,profit',
            'payout_frequency'        => 'required|in:on_sale,weekly,monthly',
            'notes'                   => 'nullable|string|max:500',
        ]);

        $vendor = ConsignmentVendor::create($request->only(
            'name', 'contact_person', 'phone', 'email', 'address',
            'default_commission_rate', 'commission_basis', 'payout_frequency', 'notes'
        ) + ['is_active' => true]);

        ActivityLogger::log('consignment_vendor_create', "Consignment vendor '{$vendor->name}' created", ConsignmentVendor::class, $vendor->id);

        return redirect()->route('consignment.index')->with('success', "Vendor '{$vendor->name}' added.");
    }

    public function show(ConsignmentVendor $consignment)
    {
        $consignment->load(['products', 'payouts' => fn ($q) => $q->latest()->take(10)]);
        $sym = Setting::get('currency_symbol', '$');

        // Unpaid sales since last payout
        $lastPayout = $consignment->payouts()->where('status', 'paid')->latest('period_end')->first();
        $since      = $lastPayout ? $lastPayout->period_end->addDay() : Carbon::create(2000, 1, 1);

        $pendingData = $this->calculatePendingPayout($consignment, $since, now());

        return view('consignment.show', compact('consignment', 'sym', 'pendingData', 'since'));
    }

    public function edit(ConsignmentVendor $consignment)
    {
        return view('consignment.edit', compact('consignment'));
    }

    public function update(Request $request, ConsignmentVendor $consignment)
    {
        $request->validate([
            'name'                    => 'required|string|max:255',
            'contact_person'          => 'nullable|string|max:255',
            'phone'                   => 'nullable|string|max:30',
            'email'                   => 'nullable|email|max:255',
            'default_commission_rate' => 'required|numeric|min:0|max:100',
            'commission_basis'        => 'required|in:sale_price,profit',
            'payout_frequency'        => 'required|in:on_sale,weekly,monthly',
            'is_active'               => 'boolean',
        ]);

        $consignment->update($request->only(
            'name', 'contact_person', 'phone', 'email', 'address',
            'default_commission_rate', 'commission_basis', 'payout_frequency', 'notes'
        ) + ['is_active' => $request->boolean('is_active', true)]);

        ActivityLogger::log('consignment_vendor_update', "Consignment vendor '{$consignment->name}' updated", ConsignmentVendor::class, $consignment->id);

        return redirect()->route('consignment.index')->with('success', 'Vendor updated.');
    }

    // ─── Payout Generation ───────────────────────────────────────────────────

    public function generatePayout(Request $request, ConsignmentVendor $consignment)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
            'notes'        => 'nullable|string|max:500',
        ]);

        $periodStart = Carbon::parse($request->period_start)->startOfDay();
        $periodEnd   = Carbon::parse($request->period_end)->endOfDay();

        $data = $this->calculatePendingPayout($consignment, $periodStart, $periodEnd);

        if ($data['items_sold'] === 0) {
            return back()->withErrors(['error' => 'No consignment sales found in this period.']);
        }

        DB::transaction(function () use ($request, $consignment, $data, $periodStart, $periodEnd) {
            $payout = ConsignmentPayout::create([
                'consignment_vendor_id'   => $consignment->id,
                'period_start'            => $periodStart->toDateString(),
                'period_end'              => $periodEnd->toDateString(),
                'total_sales_amount'      => $data['total_sales'],
                'store_commission_amount' => $data['store_commission'],
                'vendor_payout_amount'    => $data['vendor_payout'],
                'items_sold'              => $data['items_sold'],
                'status'                  => 'pending',
                'notes'                   => $request->notes,
                'created_by'              => Auth::id(),
            ]);

            ActivityLogger::log(
                'consignment_payout_generated',
                "Payout generated for '{$consignment->name}': " . Money::format($data['vendor_payout']),
                ConsignmentPayout::class,
                $payout->id
            );
        });

        return redirect()->route('consignment.show', $consignment)->with('success', 'Payout statement generated.');
    }

    public function markPayoutPaid(Request $request, ConsignmentPayout $payout)
    {
        if ($payout->status === 'paid') {
            return back()->withErrors(['error' => 'Already marked as paid.']);
        }

        $request->validate([
            'payment_method' => 'required|in:cash,bank,card',
            'paid_date'      => 'required|date',
        ]);

        DB::transaction(function () use ($request, $payout) {
            $payout->update([
                'status'         => 'paid',
                'paid_date'      => $request->paid_date,
                'payment_method' => $request->payment_method,
            ]);

            // Deduct vendor payout from cash/bank balance
            $balanceKey = $request->payment_method === 'cash' ? 'cash_balance' : 'bank_balance';
            $balance    = (int) Setting::get($balanceKey, 0);
            Setting::set($balanceKey, $balance - $payout->vendor_payout_amount, 'integer', 'finance');

            ActivityLogger::log(
                'consignment_payout_paid',
                "Consignment payout of " . Money::format($payout->vendor_payout_amount) . " paid to '{$payout->vendor->name}'",
                ConsignmentPayout::class,
                $payout->id
            );
        });

        return back()->with('success', 'Payout marked as paid.');
    }

    // ─── Report ───────────────────────────────────────────────────────────────

    public function report(Request $request)
    {
        $from = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : now()->startOfMonth();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : now()->endOfMonth();

        $vendors = ConsignmentVendor::with('products')->get();

        $report = $vendors->map(function (ConsignmentVendor $vendor) use ($from, $to) {
            return array_merge(
                ['vendor' => $vendor],
                $this->calculatePendingPayout($vendor, $from, $to)
            );
        })->filter(fn ($row) => $row['items_sold'] > 0);

        $sym = Setting::get('currency_symbol', '$');

        return view('consignment.report', compact('report', 'from', 'to', 'sym'));
    }

    // ─── Internal: calculate payout figures for a period ─────────────────────

    private function calculatePendingPayout(ConsignmentVendor $vendor, $from, $to): array
    {
        $productIds = $vendor->products()->pluck('id');

        if ($productIds->isEmpty()) {
            return ['total_sales' => 0, 'store_commission' => 0, 'vendor_payout' => 0, 'items_sold' => 0, 'breakdown' => []];
        }

        $saleItems = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from, $to])
            ->whereIn('sale_items.product_id', $productIds)
            ->select('sale_items.product_id', 'sale_items.quantity', 'sale_items.unit_price', 'sale_items.cost_price')
            ->get();

        $totalSales      = 0;
        $storeCommission = 0;
        $vendorPayout    = 0;
        $itemsSold       = 0;
        $breakdown       = [];

        foreach ($saleItems as $item) {
            $product      = Product::find($item->product_id);
            if (!$product) continue;

            $lineSales    = $item->unit_price * $item->quantity;
            $split        = $vendor->calculateSplit($product, $item->unit_price);

            $lineCommission = $split['store_commission'] * $item->quantity;
            $lineVendor     = $split['vendor_payout'] * $item->quantity;

            $totalSales      += $lineSales;
            $storeCommission += $lineCommission;
            $vendorPayout    += $lineVendor;
            $itemsSold       += $item->quantity;

            $breakdown[] = [
                'product'    => $product->name,
                'quantity'   => $item->quantity,
                'unit_price' => $item->unit_price,
                'commission' => $lineCommission,
                'payout'     => $lineVendor,
            ];
        }

        return compact('totalSales', 'storeCommission', 'vendorPayout', 'itemsSold', 'breakdown');
    }
}
