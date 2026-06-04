@extends('layouts.app')
@section('title', 'Add Owner')
@section('breadcrumb')
    <a href="{{ route('owners.index') }}" class="text-gray-500 hover:text-gray-900">Owner Dashboard</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Add Owner</span>
@endsection

@section('content')
<div class="pt-6 max-w-lg">
    <h1 class="mb-6">Add Owner</h1>

    @php
        $currentTotal = \App\Models\Owner::where('is_active', true)->sum('profit_share_percentage');
        $remaining    = max(0, 100 - $currentTotal);
    @endphp

    @if($currentTotal > 0)
    <div class="alert alert-info mb-5">
        <i data-lucide="info" class="w-4 h-4 flex-shrink-0"></i>
        Current active owners have {{ number_format($currentTotal, 2) }}% allocated.
        Remaining: <strong>{{ number_format($remaining, 2) }}%</strong>
    </div>
    @endif

    <form method="POST" action="{{ route('owners.store') }}" class="space-y-5">
        @csrf
        <div class="card">
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Owner Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input @error('name') border-red-400 @enderror"
                        value="{{ old('name') }}" required placeholder="e.g. John Smith">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Profit Share % <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="profit_share_percentage" step="0.01" min="0" max="100"
                            class="form-input w-32 @error('profit_share_percentage') border-red-400 @enderror"
                            value="{{ old('profit_share_percentage', number_format($remaining, 2)) }}" required>
                        <span class="text-sm text-gray-500">% of net profit</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                        All active owner shares should sum to 100%.
                        @if($remaining > 0) Suggested: {{ number_format($remaining, 2) }}% @endif
                    </p>
                    @error('profit_share_percentage')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Notes <span class="text-xs text-gray-400">(optional)</span></label>
                    <textarea name="notes" class="form-input" rows="2"
                        placeholder="Any notes about this owner">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('owners.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Add Owner</button>
        </div>
    </form>
</div>
@endsection
