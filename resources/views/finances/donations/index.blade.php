@extends('layouts.app')
@section('title', 'Donations')
@section('breadcrumb')
    <span class="text-gray-500">Finances</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Donations</span>
@endsection

@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol', '$'); @endphp
<div class="pt-6 space-y-4"
    x-data="{ showCreate: false, showGive: false, giveId: null, giveAmount: 0 }">

    <div class="flex items-center justify-between">
        <h1>Donations</h1>
        <button @click="showCreate = true" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i> Record Donation
        </button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="stat-card">
            <p class="stat-label">Pending</p>
            <p class="stat-value">{{ $sym }}{{ number_format($totalPending / 100, 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total Given</p>
            <p class="stat-value">{{ $sym }}{{ number_format($totalGiven / 100, 2) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card">
        <div class="card-body flex gap-3 items-end">
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select w-36">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="given" {{ request('status') === 'given' ? 'selected' : '' }}>Given</option>
                </select>
            </div>
            <button type="submit" class="btn-secondary">Filter</button>
            <a href="{{ route('finances.donations.index') }}" class="btn-secondary">Clear</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Period</th>
                    <th>Recipient</th>
                    <th>Status</th>
                    <th>Given Date</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($donations as $donation)
                    <tr>
                        <td class="text-gray-500">{{ $donation->created_at->format('M d, Y') }}</td>
                        <td class="font-medium">{{ $sym }}{{ number_format($donation->amount / 100, 2) }}</td>
                        <td class="text-gray-500 text-xs">
                            @if($donation->period_start)
                                {{ \Carbon\Carbon::parse($donation->period_start)->format('M d') }}
                                – {{ \Carbon\Carbon::parse($donation->period_end)->format('M d, Y') }}
                            @else —
                            @endif
                        </td>
                        <td>{{ $donation->recipient ?? '—' }}</td>
                        <td>
                            @if($donation->status === 'given')
                                <span class="badge badge-green">Given</span>
                            @else
                                <span class="badge badge-yellow">Pending</span>
                            @endif
                        </td>
                        <td class="text-gray-500">
                            {{ $donation->given_date ? \Carbon\Carbon::parse($donation->given_date)->format('M d, Y') : '—' }}
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1">
                                @if($donation->status === 'pending')
                                <button @click="showGive = true; giveId = {{ $donation->id }}; giveAmount = '{{ number_format($donation->amount/100,2,'.','') }}'"
                                    class="btn btn-primary btn-sm">
                                    Mark Given
                                </button>
                                <form method="POST" action="{{ route('finances.donations.destroy', $donation) }}"
                                    onsubmit="return confirm('Delete this donation?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-400 py-8">No donations recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($donations->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $donations->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- Create Modal --}}
    <div x-show="showCreate" x-cloak class="modal-backdrop" @click.self="showCreate = false">
        <div class="modal max-w-md">
            <div class="modal-header">
                <h3>Record Donation</h3>
                <button @click="showCreate = false"><i data-lucide="x" class="w-4 h-4 text-gray-400"></i></button>
            </div>
            <form method="POST" action="{{ route('finances.donations.store') }}">
                @csrf
                <div class="modal-body space-y-4">
                    <div>
                        <label class="form-label">Amount <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01" class="form-input" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Period Start</label>
                            <input type="date" name="period_start" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Period End</label>
                            <input type="date" name="period_end" class="form-input">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-input" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="showCreate = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Mark Given Modal --}}
    <div x-show="showGive" x-cloak class="modal-backdrop" @click.self="showGive = false">
        <div class="modal max-w-sm">
            <div class="modal-header">
                <h3>Mark Donation as Given</h3>
                <button @click="showGive = false"><i data-lucide="x" class="w-4 h-4 text-gray-400"></i></button>
            </div>
            <form method="POST" :action="`/finances/donations/${giveId}/give`">
                @csrf
                <div class="modal-body space-y-4">
                    <div>
                        <label class="form-label">Recipient Charity <span class="text-red-500">*</span></label>
                        <input type="text" name="recipient" class="form-input" required
                            placeholder="e.g. Red Crescent">
                    </div>
                    <div>
                        <label class="form-label">Given Date</label>
                        <input type="date" name="given_date" class="form-input"
                            value="{{ now()->toDateString() }}">
                    </div>
                    <p class="text-sm text-gray-500">Amount: {{ $sym }}<span x-text="giveAmount"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="showGive = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
