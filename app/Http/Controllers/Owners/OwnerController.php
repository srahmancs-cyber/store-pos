<?php

namespace App\Http\Controllers\Owners;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\OwnerTransaction;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OwnerController extends Controller
{
    // -------------------------------------------------------------------------
    // Owner List (management)
    // -------------------------------------------------------------------------

    public function list()
    {
        $owners           = Owner::withCount('transactions')->orderBy('sort_order')->get();
        $totalShareActive = Owner::where('is_active', true)->sum('profit_share_percentage');

        return view('owners.list', compact('owners', 'totalShareActive'));
    }

    // -------------------------------------------------------------------------
    // Dashboard
    // -------------------------------------------------------------------------

    public function index()
    {
        $owners = Owner::with(['transactions' => fn ($q) => $q->latest('transaction_date')])
            ->orderBy('sort_order')
            ->get()
            ->map(function (Owner $owner) {
                $totalInvested  = $owner->transactions->whereIn('type', ['investment'])->sum('amount');
                $totalWithdrawn = $owner->transactions->where('type', 'withdrawal')->sum('amount');
                $totalProfit    = $owner->transactions->where('type', 'profit_allocation')->sum('amount');
                $equity         = $totalInvested + $totalProfit - $totalWithdrawn;

                return [
                    'owner'           => $owner,
                    'total_invested'  => $totalInvested,
                    'total_withdrawn' => $totalWithdrawn,
                    'total_profit'    => $totalProfit,
                    'equity'          => $equity,
                ];
            });

        $totalShareAllocated = Owner::where('is_active', true)->sum('profit_share_percentage');
        $cashBalance         = (int) Setting::get('cash_balance', 0);
        $bankBalance         = (int) Setting::get('bank_balance', 0);

        return view('owners.index', compact('owners', 'cashBalance', 'bankBalance', 'totalShareAllocated'));
    }

    // -------------------------------------------------------------------------
    // Owner CRUD
    // -------------------------------------------------------------------------

    public function create()
    {
        $nextOrder = Owner::max('sort_order') + 1;

        return view('owners.create', compact('nextOrder'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                    => 'required|string|max:100',
            'profit_share_percentage' => 'required|numeric|min:0|max:100',
            'notes'                   => 'nullable|string|max:500',
        ]);

        // Warn if total shares exceed 100%
        $currentTotal = Owner::where('is_active', true)->sum('profit_share_percentage');
        $newTotal     = $currentTotal + (float) $request->profit_share_percentage;

        $owner = Owner::create([
            'name'                    => $request->name,
            'profit_share_percentage' => $request->profit_share_percentage,
            'sort_order'              => Owner::max('sort_order') + 1,
            'is_active'               => true,
            'notes'                   => $request->notes,
        ]);

        ActivityLogger::log('owner_create', "Owner '{$owner->name}' added with {$owner->profit_share_percentage}% share", Owner::class, $owner->id);

        $message = "Owner '{$owner->name}' added.";
        if ($newTotal > 100) {
            $message .= " Warning: total profit shares now exceed 100% ({$newTotal}%). Please adjust shares.";
        }

        return redirect()->route('owners.index')->with('success', $message);
    }

    public function edit(Owner $owner)
    {
        return view('owners.edit', compact('owner'));
    }

    public function update(Request $request, Owner $owner)
    {
        $request->validate([
            'name'                    => 'required|string|max:100',
            'profit_share_percentage' => 'required|numeric|min:0|max:100',
            'notes'                   => 'nullable|string|max:500',
            'is_active'               => 'boolean',
        ]);

        $owner->update([
            'name'                    => $request->name,
            'profit_share_percentage' => $request->profit_share_percentage,
            'is_active'               => $request->boolean('is_active', true),
            'notes'                   => $request->notes,
        ]);

        ActivityLogger::log('owner_update', "Owner '{$owner->name}' updated", Owner::class, $owner->id);

        // Check total shares
        $total = Owner::where('is_active', true)->sum('profit_share_percentage');
        $message = "Owner updated.";
        if (abs($total - 100) > 0.01) {
            $message .= " Warning: active owner shares total {$total}% (should be 100%).";
        }

        return redirect()->route('owners.index')->with('success', $message);
    }

    public function destroy(Owner $owner)
    {
        if ($owner->transactions()->exists()) {
            return back()->withErrors(['error' => "Cannot delete '{$owner->name}' — they have transaction records. Deactivate instead."]);
        }

        ActivityLogger::log('owner_delete', "Owner '{$owner->name}' deleted", Owner::class, $owner->id);

        $owner->delete();

        return redirect()->route('owners.index')->with('success', 'Owner removed.');
    }

    // -------------------------------------------------------------------------
    // Transactions
    // -------------------------------------------------------------------------

    public function storeInvestment(Request $request)
    {
        $request->validate([
            'owner_id'         => 'required|integer|exists:owners,id',
            'amount'           => 'required|numeric|min:0.01',
            'destination_type' => 'required|in:cash,bank',
            'transaction_date' => 'required|date',
            'notes'            => 'nullable|string|max:500',
        ]);

        $amountCents = Money::toCents($request->amount);

        DB::transaction(function () use ($request, $amountCents) {
            $owner = Owner::findOrFail($request->owner_id);

            $transaction = OwnerTransaction::create([
                'owner_id'         => $owner->id,
                'type'             => 'investment',
                'amount'           => $amountCents,
                'transaction_date' => $request->transaction_date,
                'notes'            => $request->notes,
                'created_by'       => Auth::id(),
            ]);

            $balanceKey = $request->destination_type === 'cash' ? 'cash_balance' : 'bank_balance';
            $balance    = (int) Setting::get($balanceKey, 0);
            Setting::set($balanceKey, $balance + $amountCents, 'integer', 'finance');

            ActivityLogger::log(
                'owner_investment',
                "Investment of " . Money::format($amountCents) . " by '{$owner->name}' → {$request->destination_type}",
                OwnerTransaction::class,
                $transaction->id
            );
        });

        return redirect()->route('owners.index')->with('success', 'Investment recorded.');
    }

    public function storeWithdrawal(Request $request)
    {
        $request->validate([
            'owner_id'         => 'required|integer|exists:owners,id',
            'amount'           => 'required|numeric|min:0.01',
            'source_type'      => 'required|in:cash,bank',
            'transaction_date' => 'required|date',
            'notes'            => 'nullable|string|max:500',
        ]);

        $amountCents = Money::toCents($request->amount);

        DB::transaction(function () use ($request, $amountCents) {
            $owner = Owner::findOrFail($request->owner_id);

            $transaction = OwnerTransaction::create([
                'owner_id'         => $owner->id,
                'type'             => 'withdrawal',
                'amount'           => $amountCents,
                'transaction_date' => $request->transaction_date,
                'notes'            => $request->notes,
                'created_by'       => Auth::id(),
            ]);

            $balanceKey = $request->source_type === 'cash' ? 'cash_balance' : 'bank_balance';
            $balance    = (int) Setting::get($balanceKey, 0);
            $newBalance = $balance - $amountCents;
            Setting::set($balanceKey, $newBalance, 'integer', 'finance');

            if ($newBalance < 0) {
                session()->flash('warning', Money::format(abs($newBalance)) . ' over-drawn from ' . $request->source_type . ' balance.');
            }

            ActivityLogger::log(
                'owner_withdrawal',
                "Withdrawal of " . Money::format($amountCents) . " by '{$owner->name}' from {$request->source_type}",
                OwnerTransaction::class,
                $transaction->id
            );
        });

        return redirect()->route('owners.index')->with('success', 'Withdrawal recorded.');
    }
}
