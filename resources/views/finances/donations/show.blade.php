@extends('layouts.app')
@section('title', 'Donation Detail')
@section('breadcrumb')
    <a href="{{ route('finances.donations.index') }}" class="text-gray-500 hover:text-gray-900">Donations</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Donation #{{ $donation->id }}</span>
@endsection
@section('content')
@php $sym = \App\Models\Setting::get('currency_symbol','$'); @endphp
<div class="pt-6 max-w-lg space-y-5">
    <h1>Donation #{{ $donation->id }}</h1>
    <div class="card">
        <div class="card-body grid grid-cols-2 gap-4 text-sm">
            <div><p class="text-gray-500">Amount</p><p class="text-xl font-bold mt-0.5">{{ $sym }}{{ number_format($donation->amount/100,2) }}</p></div>
            <div><p class="text-gray-500">Status</p>
                <p class="mt-0.5">
                    @if($donation->status === 'given')<span class="badge badge-green">Given</span>
                    @else<span class="badge badge-yellow">Pending</span>@endif
                </p>
            </div>
            <div><p class="text-gray-500">Recipient</p><p class="font-medium mt-0.5">{{ $donation->recipient ?? '—' }}</p></div>
            <div><p class="text-gray-500">Given Date</p><p class="mt-0.5">{{ $donation->given_date ? \Carbon\Carbon::parse($donation->given_date)->format('M d, Y') : '—' }}</p></div>
            <div><p class="text-gray-500">Period</p>
                <p class="mt-0.5">
                    @if($donation->period_start)
                        {{ \Carbon\Carbon::parse($donation->period_start)->format('M d') }} – {{ \Carbon\Carbon::parse($donation->period_end)->format('M d, Y') }}
                    @else — @endif
                </p>
            </div>
            <div><p class="text-gray-500">Notes</p><p class="mt-0.5">{{ $donation->notes ?? '—' }}</p></div>
        </div>
    </div>
    <a href="{{ route('finances.donations.index') }}" class="btn-secondary">Back to Donations</a>
</div>
@endsection
