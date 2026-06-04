@extends('layouts.app')
@section('title', 'Add Category')
@section('breadcrumb')
    <a href="{{ route('inventory.categories.index') }}" class="text-gray-500 hover:text-gray-900">Categories</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Add Category</span>
@endsection
@section('content')
<div class="pt-6 max-w-lg">
    <h1 class="mb-6">Add Category</h1>
    <form method="POST" action="{{ route('inventory.categories.store') }}" class="space-y-5">
        @csrf
        <div class="card">
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input @error('name') border-red-400 @enderror"
                        value="{{ old('name') }}" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Parent Category</label>
                    <select name="parent_id" class="form-select">
                        <option value="">— None (top-level) —</option>
                        @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('parent_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input" rows="2">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="{{ route('inventory.categories.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create Category</button>
        </div>
    </form>
</div>
@endsection
