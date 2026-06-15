@extends('layouts.app')
@section('title','Consignment Vendors')
@section('breadcrumb')
    <span class="font-medium">Consignment</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1>Consignment Vendors</h1>
            <p class="text-sm text-gray-500 mt-1">Third-party vendors who place products in your store for sale.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('consignment.report') }}" class="btn-secondary">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i> Report
            </a>
            <a href="{{ route('consignment.create') }}" class="btn-primary">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Vendor
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Vendor</th>
                    <th>Commission</th>
                    <th>Basis</th>
                    <th>Payout Frequency</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($vendors as $vendor)
                    <tr>
                        <td>
                            <p class="font-medium">{{ $vendor->name }}</p>
                            @if($vendor->contact_person)
                            <p class="text-xs text-gray-400">{{ $vendor->contact_person }}</p>
                            @endif
                        </td>
                        <td class="font-medium">{{ $vendor->default_commission_rate }}%</td>
                        <td class="capitalize text-gray-500">{{ str_replace('_', ' ', $vendor->commission_basis) }}</td>
                        <td class="capitalize text-gray-500">{{ str_replace('_', ' ', $vendor->payout_frequency) }}</td>
                        <td>{{ $vendor->products_count }}</td>
                        <td>
                            @if($vendor->is_active) <span class="badge badge-green">Active</span>
                            @else <span class="badge badge-gray">Inactive</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('consignment.show', $vendor) }}" class="btn btn-secondary btn-sm">View</a>
                                <a href="{{ route('consignment.edit', $vendor) }}" class="btn btn-secondary btn-sm">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-400 py-10">No consignment vendors yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vendors->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $vendors->links() }}</div>
        @endif
    </div>
</div>
@endsection
