@extends('layouts.app')
@section('title', $employee->name)
@section('breadcrumb')
    <a href="{{ route('employees.index') }}" class="text-gray-500 hover:text-gray-900">Employees</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">{{ $employee->name }}</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 space-y-5" x-data="{ tab: 'profile' }">
    <div class="flex items-center justify-between">
        <h1>{{ $employee->name }}</h1>
        <a href="{{ route('employees.edit', $employee) }}" class="btn-secondary">Edit</a>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-0 border-b border-gray-200">
        @foreach(['profile'=>'Profile','attendance'=>'Attendance','salaries'=>'Salaries','loans'=>'Loans'] as $key=>$label)
        <button @click="tab='{{ $key }}'"
            :class="tab==='{{ $key }}' ? 'border-b-2 border-gray-900 text-gray-900' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-2.5 text-sm font-medium -mb-px">{{ $label }}</button>
        @endforeach
    </div>

    {{-- Profile --}}
    <div x-show="tab==='profile'" class="card">
        <div class="card-body grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Name</span><p class="mt-0.5 font-medium">{{ $employee->name }}</p></div>
            <div><span class="text-gray-500">Role</span><p class="mt-0.5 capitalize">{{ $employee->role }}</p></div>
            <div><span class="text-gray-500">Email</span><p class="mt-0.5">{{ $employee->email }}</p></div>
            <div><span class="text-gray-500">Phone</span><p class="mt-0.5">{{ $employee->phone ?? '—' }}</p></div>
            <div><span class="text-gray-500">Hire Date</span><p class="mt-0.5">{{ $employee->hire_date?->format('M d, Y') ?? '—' }}</p></div>
            <div><span class="text-gray-500">Status</span><p class="mt-0.5">
                @if($employee->is_active)<span class="badge badge-green">Active</span>
                @else<span class="badge badge-red">Inactive</span>@endif
            </p></div>
            <div><span class="text-gray-500">Salary Type</span><p class="mt-0.5 capitalize">{{ $employee->salary_type }}</p></div>
            <div><span class="text-gray-500">Salary Value</span><p class="mt-0.5 font-medium">
                @if($employee->salary_type === 'commission'){{ number_format($employee->salary_value/100,2) }}%
                @else{{ $sym }}{{ number_format($employee->salary_value/100,2) }}@endif
            </p></div>
        </div>
    </div>

    {{-- Attendance --}}
    <div x-show="tab==='attendance'" class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr><th>Date</th><th>Clock In</th><th>Clock Out</th><th>Duration</th></tr></thead>
                <tbody>
                    @forelse($employee->attendance->sortByDesc('date') as $a)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($a->date)->format('M d, Y') }}</td>
                        <td>{{ $a->clock_in ? \Carbon\Carbon::parse($a->clock_in)->format('H:i') : '—' }}</td>
                        <td>{{ $a->clock_out ? \Carbon\Carbon::parse($a->clock_out)->format('H:i') : '<span class="badge badge-yellow">Active</span>' }}</td>
                        <td>{{ $a->duration_minutes ? floor($a->duration_minutes/60).'h '.($a->duration_minutes%60).'m' : '—' }}</td>
                    </tr>
                    @empty<tr><td colspan="4" class="text-center text-gray-400 py-6">No attendance records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Salaries --}}
    <div x-show="tab==='salaries'" class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr><th>Period</th><th>Amount</th><th>Method</th><th>Paid Date</th></tr></thead>
                <tbody>
                    @forelse($employee->salaryPayments->sortByDesc('paid_date') as $sp)
                    <tr>
                        <td>{{ $sp->period_month }}/{{ $sp->period_year }}</td>
                        <td class="font-medium">{{ $sym }}{{ number_format($sp->amount/100,2) }}</td>
                        <td class="capitalize text-gray-500">{{ $sp->payment_method }}</td>
                        <td class="text-gray-500">{{ \Carbon\Carbon::parse($sp->paid_date)->format('M d, Y') }}</td>
                    </tr>
                    @empty<tr><td colspan="4" class="text-center text-gray-400 py-6">No salary payments.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Loans --}}
    <div x-show="tab==='loans'" class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr><th>Amount</th><th>Remaining</th><th>Source</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($employee->loans->sortByDesc('created_at') as $loan)
                    <tr>
                        <td>{{ $sym }}{{ number_format($loan->amount/100,2) }}</td>
                        <td class="font-medium">{{ $sym }}{{ number_format($loan->remaining_balance/100,2) }}</td>
                        <td class="capitalize text-gray-500">{{ str_replace('_',' ',$loan->source_type) }}</td>
                        <td>
                            @if($loan->status==='outstanding')<span class="badge badge-yellow">Outstanding</span>
                            @elseif($loan->status==='repaid')<span class="badge badge-green">Repaid</span>
                            @else<span class="badge badge-red">Written Off</span>@endif
                        </td>
                        <td class="text-gray-500">{{ $loan->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty<tr><td colspan="5" class="text-center text-gray-400 py-6">No loans.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
