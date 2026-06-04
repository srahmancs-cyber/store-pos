@extends('layouts.app')
@section('title', 'Salaries')
@section('breadcrumb')
    <span class="text-gray-500">Employees</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Salaries</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 space-y-4" x-data="{ showPayModal: false, payEmployee: null, payAmount: 0, payMethod: 'bank', payDate: '{{ now()->toDateString() }}' }">
    <div class="flex items-center justify-between">
        <div>
            <h1>Salaries</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
            </p>
        </div>
        <form method="GET" class="flex gap-2 items-end">
            <div>
                <label class="form-label text-xs">Month</label>
                <select name="month" class="form-select text-sm">
                    @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="form-label text-xs">Year</label>
                <input type="number" name="year" class="form-input w-24 text-sm" value="{{ $year }}" min="2020" max="2030">
            </div>
            <button type="submit" class="btn-secondary btn-sm">Go</button>
        </form>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Employee</th>
                    <th>Salary Type</th>
                    <th>Calculated Amount</th>
                    <th>Status This Month</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @foreach($employeeData as $row)
                    @php
                        $emp = $row['employee'];
                        $paid = $emp->salaryPayments
                            ->where('period_month', $month)
                            ->where('period_year', $year)
                            ->first();
                    @endphp
                    <tr>
                        <td class="font-medium">{{ $emp->name }}</td>
                        <td class="capitalize text-gray-500">{{ $emp->salary_type }}</td>
                        <td class="font-medium">
                            @if($emp->salary_type === 'commission')
                                {{ number_format($emp->salary_value/100,2) }}%
                                <span class="text-xs text-gray-400 ml-1">(of sales)</span>
                            @else
                                {{ $sym }}{{ number_format($row['calculated_salary']/100,2) }}
                            @endif
                        </td>
                        <td>
                            @if($row['already_paid'] > 0)
                                <span class="badge badge-green">Paid {{ $sym }}{{ number_format($row['already_paid']/100,2) }}</span>
                                @if($row['balance_due'] > 0)
                                <span class="badge badge-yellow ml-1">Due {{ $sym }}{{ number_format($row['balance_due']/100,2) }}</span>
                                @endif
                            @else
                                <span class="badge badge-yellow">Pending</span>
                            @endif
                        </td>
                        <td class="text-right">
                            @if($row['balance_due'] > 0)
                            <button @click="
                                payEmployee = {{ $emp->id }};
                                payAmount = {{ number_format($row['balance_due']/100,2,'.','') }};
                                showPayModal = true"
                                class="btn btn-primary btn-sm">
                                Mark as Paid
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pay Modal --}}
    <div x-show="showPayModal" x-cloak class="modal-backdrop" @click.self="showPayModal=false">
        <div class="modal">
            <div class="modal-header">
                <h3>Record Salary Payment</h3>
                <button @click="showPayModal=false"><i data-lucide="x" class="w-4 h-4 text-gray-400"></i></button>
            </div>
            <form method="POST" action="{{ route('employees.salaries.pay') }}">
                @csrf
                <div class="modal-body space-y-4">
                    <input type="hidden" name="employee_id" :value="payEmployee">
                    <input type="hidden" name="period_month" value="{{ $month }}">
                    <input type="hidden" name="period_year" value="{{ $year }}">
                    <div>
                        <label class="form-label">Amount <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" x-model="payAmount" class="form-input" min="0" step="0.01" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" x-model="payMethod" class="form-select">
                                <option value="bank">Bank Transfer</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Date</label>
                            <input type="date" name="paid_date" x-model="payDate" class="form-input">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="showPayModal=false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
