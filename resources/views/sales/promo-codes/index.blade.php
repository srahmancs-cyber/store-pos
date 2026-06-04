@extends('layouts.app')
@section('title','Promo Codes')
@section('breadcrumb')
    <span class="text-gray-500">Sales</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Promo Codes</span>
@endsection
@section('content')
<div class="pt-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1>Promo Codes</h1>
        <a href="{{ route('promo-codes.create') }}" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i> New Code
        </a>
    </div>
    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Code</th>
                    <th>Discount</th>
                    <th>Min Order</th>
                    <th>Uses</th>
                    <th>Validity</th>
                    <th>Status</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($codes as $code)
                    <tr>
                        <td class="font-mono font-semibold">{{ $code->code }}</td>
                        <td>
                            @if($code->discount_type === 'percentage')
                                {{ number_format($code->discount_value / 100, 2) }}%
                            @else
                                {{ $sym }}{{ number_format($code->discount_value / 100, 2) }}
                            @endif
                        </td>
                        <td>{{ $code->min_order_amount > 0 ? $sym.number_format($code->min_order_amount/100,2) : '—' }}</td>
                        <td>
                            {{ $code->used_count }}
                            @if($code->max_uses) / {{ $code->max_uses }} @else / ∞ @endif
                        </td>
                        <td class="text-xs text-gray-500">
                            @if($code->starts_at) From {{ $code->starts_at->format('M d, Y') }}<br> @endif
                            @if($code->expires_at)
                                <span class="{{ $code->expires_at->isPast() ? 'text-red-500' : '' }}">
                                    Until {{ $code->expires_at->format('M d, Y') }}
                                </span>
                            @else No expiry @endif
                        </td>
                        <td>
                            @if(!$code->is_active) <span class="badge badge-gray">Inactive</span>
                            @elseif($code->expires_at && $code->expires_at->isPast()) <span class="badge badge-red">Expired</span>
                            @elseif($code->max_uses && $code->used_count >= $code->max_uses) <span class="badge badge-yellow">Exhausted</span>
                            @else <span class="badge badge-green">Active</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('promo-codes.edit', $code) }}" class="btn btn-secondary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('promo-codes.destroy', $code) }}"
                                    onsubmit="return confirm('Delete code {{ $code->code }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-400 py-8">No promo codes yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($codes->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $codes->links() }}</div>
        @endif
    </div>
</div>
@endsection
