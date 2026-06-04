@extends('layouts.app')
@section('title', 'Edit Owner')
@section('breadcrumb')
    <a href="{{ route('owners.index') }}" class="text-gray-500 hover:text-gray-900">Owner Dashboard</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Edit — {{ $owner->name }}</span>
@endsection

@section('content')
<div class="pt-6 max-w-lg">
    <h1 class="mb-6">Edit Owner</h1>

    @php
        $currentTotal = \App\Models\Owner::where('is_active', true)
            ->where('id', '!=', $owner->id)
            ->sum('profit_share_percentage');
        $remaining = max(0, 100 - $currentTotal);
    @endphp

    <div class="alert alert-info mb-5">
        <i data-lucide="info" class="w-4 h-4 flex-shrink-0"></i>
        Other active owners hold {{ number_format($currentTotal, 2) }}%.
        This owner can hold up to <strong>{{ number_format($remaining, 2) }}%</strong> to reach 100%.
    </div>

    <form method="POST" action="{{ route('owners.update', $owner) }}" class="space-y-5">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Owner Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input @error('name') border-red-400 @enderror"
                        value="{{ old('name', $owner->name) }}" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Profit Share % <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="profit_share_percentage" step="0.01" min="0" max="100"
                            class="form-input w-32 @error('profit_share_percentage') border-red-400 @enderror"
                            value="{{ old('profit_share_percentage', $owner->profit_share_percentage) }}" required>
                        <span class="text-sm text-gray-500">% of net profit</span>
                    </div>
                    @error('profit_share_percentage')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3 pt-1">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active', $owner->is_active) ? 'checked' : '' }}
                        class="rounded border-gray-300">
                    <label for="is_active" class="text-sm text-gray-700">Active (included in profit allocation)</label>
                </div>

                <div>
                    <label class="form-label">Notes <span class="text-xs text-gray-400">(optional)</span></label>
                    <textarea name="notes" class="form-input" rows="2">{{ old('notes', $owner->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('owners.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
