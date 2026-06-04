@extends('layouts.app')

@section('title', 'Employees')

@section('breadcrumb')
    <i data-lucide="users" class="w-4 h-4"></i>
    Employees
@endsection

@section('content')
<div class="pt-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Employees</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage your team members</p>
        </div>
        <a href="{{ route('employees.create') }}" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Employee
        </a>
    </div>

    {{-- Filters --}}
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('employees.index') }}" class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email or phone..." class="form-input">
                </div>
                <div class="w-48">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                        <option value="manager" @selected(request('role') === 'manager')>Manager</option>
                        <option value="cashier" @selected(request('role') === 'cashier')>Cashier</option>
                    </select>
                </div>
                <div class="w-40">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" @selected(request('is_active') === '1')>Active</option>
                        <option value="0" @selected(request('is_active') === '0')>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    Filter
                </button>
                @if(request()->hasAny(['search', 'role', 'is_active']))
                    <a href="{{ route('employees.index') }}" class="btn-secondary">Clear</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Salary Type</th>
                            <th>Salary Value</th>
                            <th>Hire Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                        <tr>
                            <td>
                                <div class="font-medium text-gray-900">{{ $employee->name }}</div>
                                <div class="text-xs text-gray-500">{{ $employee->email }}</div>
                            </td>
                            <td>
                                @if($employee->role === 'admin')
                                    <span class="badge badge-blue">Admin</span>
                                @elseif($employee->role === 'manager')
                                    <span class="badge badge-yellow">Manager</span>
                                @else
                                    <span class="badge badge-gray">Cashier</span>
                                @endif
                            </td>
                            <td class="capitalize text-gray-700">{{ $employee->salary_type }}</td>
                            <td class="font-mono text-gray-700">
                                ${{ number_format($employee->salary_value / 100, 2) }}
                                @if($employee->salary_type === 'hourly')<span class="text-xs text-gray-400">/hr</span>@endif
                                @if($employee->salary_type === 'commission')<span class="text-xs text-gray-400">%</span>@endif
                            </td>
                            <td class="text-gray-500 text-sm">
                                {{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('M d, Y') : '—' }}
                            </td>
                            <td>
                                @if($employee->is_active)
                                    <span class="badge badge-green">Active</span>
                                @else
                                    <span class="badge badge-red">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('employees.show', $employee) }}" class="btn-secondary btn-sm">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('employees.edit', $employee) }}" class="btn-secondary btn-sm">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm('Delete this employee?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-gray-400 py-10">
                                <i data-lucide="users" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                                <p>No employees found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($employees->hasPages())
        <div class="card-body border-t border-gray-100">
            {{ $employees->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
