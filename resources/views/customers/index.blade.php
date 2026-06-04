@extends('layouts.app')
@section('title','Customers')
@section('breadcrumb')<span class="font-medium">Customers</span>@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1>Customers</h1>
        <a href="{{ route('customers.create') }}" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Customer
        </a>
    </div>
    <form method="GET" class="card">
        <div class="card-body flex gap-3 items-end">
            <div class="flex-1"><label class="form-label">Search</label>
                <input type="text" name="search" class="form-input" placeholder="Name, phone, or email…" value="{{ request('search') }}"></div>
            <button type="submit" class="btn-secondary">Search</button>
            <a href="{{ route('customers.index') }}" class="btn-secondary">Clear</a>
        </div>
    </form>
    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Total Sales</th><th></th></tr></thead>
                <tbody>
                    @forelse($customers as $c)
                    <tr>
                        <td class="font-medium">{{ $c->name ?? '—' }}</td>
                        <td class="text-gray-500">{{ $c->phone ?? '—' }}</td>
                        <td class="text-gray-500">{{ $c->email ?? '—' }}</td>
                        <td>{{ $c->sales_count }}</td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('customers.show', $c) }}" class="btn btn-secondary btn-sm">View</a>
                                <a href="{{ route('customers.edit', $c) }}" class="btn btn-secondary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('customers.destroy', $c) }}"
                                    onsubmit="return confirm('Delete this customer?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-gray-400 py-8">No customers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $customers->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
