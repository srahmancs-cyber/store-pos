<?php

namespace App\Http\Controllers\Finances;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\CapitalInjection;
use App\Models\Owner;
use App\Models\OwnerTransaction;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CapitalInjectionController extends Controller
{
    public function index()
    {
        $injections = CapitalInjection::with(['creator', 'ownerTransaction.owner'])
            ->latest()
            ->paginate(20);

        return view('finances.capital-injections.index', compact('injections'));
    }

    public function create()
    {
        $owners = Owner::orderBy('sort_order')->get();

        return view('finances.capital-injections.create', compact('owners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount'           => 'required|numeric|min:0.01',
            'source_type'      => 'required|in:owner,external',
            'source_id'        => 'required_if:source_type,owner|nullable|integer|exists:owners,id',
            'destination_type' => 'required|in:cash,bank',
            'purpose'          => 'nullable|string|max:500',
            'transaction_date' => 'required|date',
        ]);

        $amountCents = Money::toCents($request->amount);

        DB::transaction(function () use ($request, $amountCents) {
            $injection = CapitalInjection::create([
                'amount'           => $amountCents,
                'source_type'      => $request->source_type,
                'source_id'        => $request->source_type === 'owner' ? $request->source_id : null,
                'destination_type' => $request->destination_type,
                'purpose'          => $request->purpose,
                'transaction_date' => $request->transaction_date,
                'created_by'       => Auth::id(),
            ]);

            // Add to cash or bank balance
            $balanceKey = $request->destination_type === 'cash' ? 'cash_balance' : 'bank_balance';
            $balance    = (int) Setting::get($balanceKey, 0);
            Setting::set($balanceKey, $balance + $amountCents, 'integer', 'finance');

            // If owner injection, create linked OwnerTransaction
            if ($request->source_type === 'owner' && $request->source_id) {
                OwnerTransaction::create([
                    'owner_id'             => $request->source_id,
                    'type'                 => 'investment',
                    'amount'               => $amountCents,
                    'transaction_date'     => $request->transaction_date,
                    'notes'                => $request->purpose ?? 'Capital injection',
                    'capital_injection_id' => $injection->id,
                    'created_by'           => Auth::id(),
                ]);
            }

            ActivityLogger::log(
                'capital_injection',
                "Capital injection: " . Money::format($amountCents) .
                " from {$request->source_type} to {$request->destination_type}",
                CapitalInjection::class,
                $injection->id
            );
        });

        return redirect()->route('finances.capital-injections.index')->with('success', 'Capital injection recorded.');
    }
}
