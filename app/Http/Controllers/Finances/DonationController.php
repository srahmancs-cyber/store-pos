<?php

namespace App\Http\Controllers\Finances;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        $query = Donation::latest('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $donations    = $query->paginate(20)->withQueryString();
        $totalPending = Donation::where('status', 'pending')->sum('amount');
        $totalGiven   = Donation::where('status', 'given')->sum('amount');

        return view('finances.donations.index', compact('donations', 'totalPending', 'totalGiven'));
    }

    public function create()
    {
        return view('finances.donations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount'       => 'required|numeric|min:0.01',
            'period_start' => 'nullable|date',
            'period_end'   => 'nullable|date|after_or_equal:period_start',
            'notes'        => 'nullable|string|max:500',
        ]);

        $donation = Donation::create([
            'amount'                 => Money::toCents($request->amount),
            'calculated_from_profit' => null,
            'period_start'           => $request->period_start,
            'period_end'             => $request->period_end,
            'status'                 => 'pending',
            'notes'                  => $request->notes,
        ]);

        ActivityLogger::log('donation_create', "Donation created: " . Money::format($donation->amount), Donation::class, $donation->id);

        return redirect()->route('finances.donations.index')->with('success', 'Donation recorded as pending.');
    }

    public function markGiven(Request $request, Donation $donation)
    {
        if ($donation->status === 'given') {
            return back()->withErrors(['error' => 'Donation already marked as given.']);
        }

        $request->validate([
            'recipient'  => 'required|string|max:255',
            'given_date' => 'nullable|date',
        ]);

        $donation->update([
            'status'     => 'given',
            'given_date' => $request->given_date ?? Carbon::today(),
            'recipient'  => $request->recipient,
        ]);

        ActivityLogger::log('donation_given', "Donation of " . Money::format($donation->amount) . " given to '{$request->recipient}'", Donation::class, $donation->id);

        return redirect()->route('finances.donations.index')->with('success', 'Donation marked as given.');
    }

    public function destroy(Donation $donation)
    {
        if ($donation->status === 'given') {
            return back()->withErrors(['error' => 'Cannot delete a donation that has already been given.']);
        }

        ActivityLogger::log('donation_delete', "Donation deleted: " . Money::format($donation->amount), Donation::class, $donation->id);

        $donation->delete();

        return redirect()->route('finances.donations.index')->with('success', 'Donation deleted.');
    }
}
