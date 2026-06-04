@extends('layouts.app')
@section('title', 'Activity Log')
@section('breadcrumb')
    <span class="text-gray-500">System</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Activity Log</span>
@endsection
@section('content')
<div class="pt-6 space-y-4">
    <h1>Activity Log</h1>

    <form method="GET" class="card">
        <div class="card-body flex flex-wrap gap-3 items-end">
            <div>
                <label class="form-label">User</label>
                <select name="user_id" class="form-select w-44">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Action</label>
                <select name="action_type" class="form-select w-44">
                    <option value="">All Actions</option>
                    @foreach($actionTypes as $type)
                    <option value="{{ $type }}" {{ request('action_type') === $type ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $type)) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">From</label>
                <input type="date" name="date_from" class="form-input" value="{{ request('date_from') }}">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" name="date_to" class="form-input" value="{{ request('date_to') }}">
            </div>
            <button type="submit" class="btn-secondary">Filter</button>
            <a href="{{ route('activity-log') }}" class="btn-secondary">Clear</a>
        </div>
    </form>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Date & Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr></thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="text-gray-500 text-xs whitespace-nowrap">
                            {{ $log->created_at->format('M d, Y H:i:s') }}
                        </td>
                        <td class="font-medium">{{ $log->user?->name ?? 'System' }}</td>
                        <td>
                            @php
                                $actionColors = [
                                    'login' => 'badge-green',
                                    'logout' => 'badge-gray',
                                    'sale_void' => 'badge-red',
                                    'sale_create' => 'badge-green',
                                    'employee_create' => 'badge-blue',
                                    'employee_delete' => 'badge-red',
                                    'settings_update' => 'badge-yellow',
                                    'access_denied' => 'badge-red',
                                    'capital_injection' => 'badge-blue',
                                    'owner_investment' => 'badge-green',
                                    'owner_withdrawal' => 'badge-yellow',
                                    'profit_calculation' => 'badge-blue',
                                ];
                                $color = $actionColors[$log->action_type] ?? 'badge-gray';
                            @endphp
                            <span class="badge {{ $color }}">
                                {{ ucfirst(str_replace('_', ' ', $log->action_type)) }}
                            </span>
                        </td>
                        <td class="text-gray-600 max-w-md truncate">{{ $log->description }}</td>
                        <td class="text-gray-400 text-xs font-mono">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-gray-400 py-8">No activity logged yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $logs->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
