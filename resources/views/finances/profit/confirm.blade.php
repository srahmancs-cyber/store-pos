@extends('layouts.app')
@section('title', 'Confirm Recalculation')
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol', '$'); @endphp
<div class="pt-6 max-w-lg">
    <div class="card">
        <div class="card-header">
            <h3>Confirm Recalculation</h3>
        </div>
        <div class="card-body space-y-4">
            <div class="alert alert-warning">
                <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0"></i>
                A calculation already exists for this period. Proceeding will overwrite the existing result and
                create new owner profit allocations.
            </div>
            <div class="text-sm space-y-1">
                <p><span class="text-gray-500">Period:</span>
                    <strong>{{ \Carbon\Carbon::parse($period_start)->format('M d, Y') }}
                    – {{ \Carbon\Carbon::parse($period_end)->format('M d, Y') }}</strong></p>
                <p><span class="text-gray-500">Existing Net Profit:</span>
                    <strong>{{ $sym }}{{ number_format($existing->net_profit / 100, 2) }}</strong></p>
            </div>
        </div>
        <div class="card-body pt-0 flex gap-3 justify-end border-t border-gray-100">
            <a href="{{ route('finances.profit') }}" class="btn-secondary">Cancel</a>
            <form method="POST" action="{{ route('finances.profit.calculate') }}">
                @csrf
                <input type="hidden" name="period_start" value="{{ $period_start }}">
                <input type="hidden" name="period_end" value="{{ $period_end }}">
                <input type="hidden" name="other_income" value="{{ $other_income }}">
                <input type="hidden" name="confirm" value="1">
                <button type="submit" class="btn-danger">Recalculate & Overwrite</button>
            </form>
        </div>
    </div>
</div>
@endsection
