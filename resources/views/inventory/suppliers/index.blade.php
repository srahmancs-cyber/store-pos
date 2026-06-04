@extends('layouts.app')
@section('title', 'Suppliers')
@section('breadcrumb')
    <span class="text-gray-500">Inventory</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Suppliers</span>
@endsection
@section('content')
<div class="pt-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1>Suppliers</h1>
        <a href="{{ route('inventory.suppliers.create') }}" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Supplier
        </a>
    </div>
    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Lead Time</th>
                    <th>Status</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($suppliers as $sup)
                    <tr>
                        <td class="font-medium">{{ $sup->name }}</td>
                        <td class="text-gray-500">{{ $sup->contact_person ?? '—' }}</td>
                        <td class="text-gray-500">{{ $sup->phone ?? '—' }}</td>
                        <td class="text-gray-500">{{ $sup->email ?? '—' }}</td>
                        <td>{{ $sup->lead_time_days ? $sup->lead_time_days.' days' : '—' }}</td>
                        <td>
                            @if($sup->is_active) <span class="badge badge-green">Active</span>
                            @else <span class="badge badge-gray">Inactive</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('inventory.suppliers.edit', $sup) }}" class="btn btn-secondary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('inventory.suppliers.destroy', $sup) }}"
                                    onsubmit="return confirm('Delete this supplier?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-400 py-8">No suppliers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $suppliers->links() }}</div>
        @endif
    </div>
</div>
@endsection
