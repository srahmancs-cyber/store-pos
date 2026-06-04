@extends('layouts.app')
@section('title', 'Quotations')
@section('breadcrumb')
    <span class="text-gray-500">Sales</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Quotations</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol', '$'); @endphp
<div class="pt-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1>Quotations</h1>
        <a href="{{ route('quotations.create') }}" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i> New Quotation
        </a>
    </div>

    <form method="GET" class="card">
        <div class="card-body flex gap-3 items-end">
            <div class="flex-1">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-input" placeholder="Reference or ID…"
                    value="{{ request('search') }}">
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select w-36">
                    <option value="">All</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <button type="submit" class="btn-secondary">Filter</button>
            <a href="{{ route('quotations.index') }}" class="btn-secondary">Clear</a>
        </div>
    </form>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>#</th>
                    <th>Reference</th>
                    <th>Customer</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th>Expires</th>
                    <th>Date</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($quotations as $q)
                    <tr>
                        <td class="font-mono font-medium">#{{ $q->id }}</td>
                        <td>{{ $q->customer_reference ?? '—' }}</td>
                        <td class="text-gray-500">{{ $q->customer?->name ?? 'Walk-in' }}</td>
                        <td class="text-gray-500">{{ $q->user?->name }}</td>
                        <td>
                            @if($q->status === 'open') <span class="badge badge-gray">Open</span>
                            @elseif($q->status === 'sent') <span class="badge badge-blue">Sent</span>
                            @elseif($q->status === 'converted') <span class="badge badge-green">Converted</span>
                            @elseif($q->status === 'expired') <span class="badge badge-red">Expired</span>
                            @else <span class="badge badge-gray">{{ ucfirst($q->status) }}</span>
                            @endif
                        </td>
                        <td class="text-gray-500 text-xs">
                            @if($q->expires_at)
                                @if($q->expires_at->isPast())
                                    <span class="text-red-500">{{ $q->expires_at->format('M d, Y') }}</span>
                                @else
                                    {{ $q->expires_at->format('M d, Y') }}
                                @endif
                            @else —
                            @endif
                        </td>
                        <td class="text-gray-500">{{ $q->created_at->format('M d, Y') }}</td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('quotations.show', $q) }}" class="btn btn-secondary btn-sm">View</a>
                                @if($q->status !== 'converted')
                                <form method="POST" action="{{ route('quotations.convert', $q) }}">
                                    @csrf
                                    <button class="btn btn-primary btn-sm">Convert</button>
                                </form>
                                <form method="POST" action="{{ route('quotations.destroy', $q) }}"
                                    onsubmit="return confirm('Delete quotation #{{ $q->id }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-gray-400 py-8">No quotations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotations->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $quotations->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
