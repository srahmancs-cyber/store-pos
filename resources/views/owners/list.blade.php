@extends('layouts.app')
@section('title', 'Owner List')
@section('breadcrumb')
    <a href="{{ route('owners.index') }}" class="text-gray-500 hover:text-gray-900">Owner Dashboard</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Owner List</span>
@endsection

@section('content')
<div class="pt-6 space-y-4">

    <div class="flex items-center justify-between">
        <div>
            <h1>Owner List</h1>
            @if(abs($totalShareActive - 100) > 0.01)
            <p class="text-sm text-yellow-600 mt-1 flex items-center gap-1">
                <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                Active owner shares total {{ number_format($totalShareActive, 2) }}% — should be 100%.
            </p>
            @else
            <p class="text-sm text-gray-400 mt-1">Active shares: {{ number_format($totalShareActive, 2) }}% / 100%</p>
            @endif
        </div>
        <a href="{{ route('owners.create') }}" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Owner
        </a>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Name</th>
                    <th>Profit Share</th>
                    <th>Transactions</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($owners as $owner)
                    <tr>
                        <td class="font-medium">{{ $owner->name }}</td>
                        <td>
                            <span class="font-medium">{{ $owner->profit_share_percentage }}%</span>
                        </td>
                        <td class="text-gray-500">{{ $owner->transactions_count }}</td>
                        <td>
                            @if($owner->is_active)
                                <span class="badge badge-green">Active</span>
                            @else
                                <span class="badge badge-gray">Inactive</span>
                            @endif
                        </td>
                        <td class="text-gray-400 text-sm max-w-xs truncate">{{ $owner->notes ?? '—' }}</td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('owners.edit', $owner) }}" class="btn btn-secondary btn-sm">Edit</a>
                                @if(!$owner->transactions_count)
                                <form method="POST" action="{{ route('owners.destroy', $owner) }}"
                                    onsubmit="return confirm('Delete {{ addslashes($owner->name) }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-gray-400 py-10">
                            No owners yet.
                            <a href="{{ route('owners.create') }}" class="underline ml-1">Add the first owner</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
