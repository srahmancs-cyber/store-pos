@extends('layouts.app')
@section('title','Expenses')
@section('breadcrumb')
    <span class="text-gray-500">Finances</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Expenses</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1>Expenses</h1>
        <a href="{{ route('finances.expenses.create') }}" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Expense
        </a>
    </div>

    <form method="GET" class="card">
        <div class="card-body flex flex-wrap gap-3 items-end">
            <div><label class="form-label">From</label><input type="date" name="date_from" class="form-input" value="{{ request('date_from') }}"></div>
            <div><label class="form-label">To</label><input type="date" name="date_to" class="form-input" value="{{ request('date_to') }}"></div>
            <div class="w-40">
                <label class="form-label">Category</label>
                <select name="category" class="form-select">
                    <option value="">All</option>
                    @foreach(['utilities','rent','internet','marketing','repairs','supplies','other'] as $cat)
                    <option value="{{ $cat }}" {{ request('category')===$cat?'selected':'' }}>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-secondary">Filter</button>
            <a href="{{ route('finances.expenses.index') }}" class="btn-secondary">Clear</a>
        </div>
    </form>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Recurring</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($expenses as $expense)
                    <tr>
                        <td class="text-gray-500">{{ \Carbon\Carbon::parse($expense->date)->format('M d, Y') }}</td>
                        <td><span class="badge badge-gray capitalize">{{ $expense->category }}</span></td>
                        <td class="max-w-xs truncate">{{ $expense->description }}</td>
                        <td class="font-medium">{{ $sym }}{{ number_format($expense->amount/100,2) }}</td>
                        <td class="capitalize text-gray-500">{{ $expense->payment_method }}</td>
                        <td>
                            @if($expense->is_recurring)
                                <i data-lucide="repeat" class="w-4 h-4 text-gray-400"></i>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('finances.expenses.edit', $expense) }}" class="btn btn-secondary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('finances.expenses.destroy', $expense) }}"
                                    onsubmit="return confirm('Delete this expense?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-400 py-8">No expenses found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $expenses->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
