@extends('layouts.app')
@section('title', 'Employee Loans')
@section('breadcrumb')
    <span class="text-gray-500">Employees</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Loans</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 space-y-4"
    x-data="{ showNew: false, showRepay: false, repayLoanId: null, repayMax: 0 }">

    <div class="flex items-center justify-between">
        <h1>Employee Loans</h1>
        <button @click="showNew=true" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i> New Loan
        </button>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Employee</th>
                    <th>Loan Amount</th>
                    <th>Remaining</th>
                    <th>Source</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($loans as $loan)
                    <tr>
                        <td class="font-medium">{{ $loan->employee?->name }}</td>
                        <td>{{ $sym }}{{ number_format($loan->amount/100,2) }}</td>
                        <td class="font-medium">{{ $sym }}{{ number_format($loan->remaining_balance/100,2) }}</td>
                        <td class="capitalize text-gray-500">{{ str_replace('_',' ',$loan->source_type) }}</td>
                        <td>
                            @if($loan->status==='outstanding')<span class="badge badge-yellow">Outstanding</span>
                            @elseif($loan->status==='repaid')<span class="badge badge-green">Repaid</span>
                            @else<span class="badge badge-red">Written Off</span>@endif
                        </td>
                        <td class="text-gray-500">{{ $loan->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="flex justify-end gap-1">
                                @if($loan->status==='outstanding')
                                <button @click="showRepay=true; repayLoanId={{ $loan->id }}; repayMax={{ number_format($loan->remaining_balance/100,2,'.','') }}"
                                    class="btn btn-secondary btn-sm">Repay</button>
                                @if(auth()->user()->role==='admin')
                                <form method="POST" action="{{ route('employees.loans.write-off', $loan) }}"
                                    onsubmit="return confirm('Write off this loan? This cannot be undone.')">
                                    @csrf
                                    <button class="btn btn-danger btn-sm">Write Off</button>
                                </form>
                                @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-400 py-8">No loans found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($loans->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $loans->links() }}</div>
        @endif
    </div>

    {{-- New Loan Modal --}}
    <div x-show="showNew" x-cloak class="modal-backdrop" @click.self="showNew=false">
        <div class="modal">
            <div class="modal-header">
                <h3>New Employee Loan</h3>
                <button @click="showNew=false"><i data-lucide="x" class="w-4 h-4 text-gray-400"></i></button>
            </div>
            <form method="POST" action="{{ route('employees.loans.store') }}">
                @csrf
                <div class="modal-body space-y-4">
                    <div>
                        <label class="form-label">Employee <span class="text-red-500">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select employee</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Amount <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" class="form-input" min="0.01" step="0.01" required>
                        </div>
                        <div>
                            <label class="form-label">Source <span class="text-red-500">*</span></label>
                            <select name="source_type" class="form-select" required>
                                <option value="bank">Bank</option>
                                <option value="cash_drawer">Cash Drawer</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-input" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="showNew=false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Issue Loan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Repay Modal --}}
    <div x-show="showRepay" x-cloak class="modal-backdrop" @click.self="showRepay=false">
        <div class="modal max-w-sm">
            <div class="modal-header">
                <h3>Record Repayment</h3>
                <button @click="showRepay=false"><i data-lucide="x" class="w-4 h-4 text-gray-400"></i></button>
            </div>
            <form method="POST" :action="`/employees/loans/${repayLoanId}/repay`">
                @csrf
                <div class="modal-body space-y-4">
                    <div>
                        <label class="form-label">Amount <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" :max="repayMax" :value="repayMax"
                            class="form-input" min="0.01" step="0.01" required>
                        <p class="text-xs text-gray-400 mt-1">Maximum: {{ $sym }}<span x-text="repayMax"></span></p>
                    </div>
                    <div>
                        <label class="form-label">Destination</label>
                        <select name="destination_type" class="form-select">
                            <option value="bank">Bank</option>
                            <option value="cash_drawer">Cash Drawer</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-input">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="showRepay=false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Record Repayment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
