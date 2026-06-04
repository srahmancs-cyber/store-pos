@extends('layouts.app')
@section('title', 'Held Carts')
@section('breadcrumb')
    <a href="{{ route('sales.create') }}" class="text-gray-500 hover:text-gray-900">New Sale</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Held Carts</span>
@endsection
@section('content')
<div class="pt-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1>Held Carts</h1>
        <a href="{{ route('sales.create') }}" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i> New Sale
        </a>
    </div>
    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Name / Reference</th>
                    <th>Items</th>
                    <th>Held By</th>
                    <th>Held At</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($heldCarts as $cart)
                    <tr>
                        <td class="font-medium">{{ $cart->name ?? 'Unnamed Cart' }}</td>
                        <td>{{ is_array($cart->cart_data) ? count($cart->cart_data) : '—' }} items</td>
                        <td class="text-gray-500">{{ $cart->user?->name }}</td>
                        <td class="text-gray-500">{{ $cart->created_at->format('M d, Y H:i') }}</td>
                        <td class="text-right">
                            <a href="{{ route('sales.restore-cart', $cart->id) }}" class="btn btn-primary btn-sm">
                                <i data-lucide="play" class="w-3.5 h-3.5"></i> Restore
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-gray-400 py-10">No held carts.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
