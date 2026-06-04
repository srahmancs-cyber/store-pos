@extends('layouts.app')
@section('title','Edit Category')
@section('breadcrumb')
    <a href="{{ route('inventory.categories.index') }}" class="text-gray-500 hover:text-gray-900">Categories</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Edit</span>
@endsection
@section('content')
<div class="pt-6 max-w-lg">
    <h1 class="mb-6">Edit Category</h1>
    <form method="POST" action="{{ route('inventory.categories.update', $category) }}" class="card">
        @csrf @method('PUT')
        <div class="card-body space-y-4">
            <div>
                <label class="form-label">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" class="form-input" required value="{{ old('name', $category->name) }}">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Parent Category</label>
                <select name="parent_id" class="form-select">
                    <option value="">— None —</option>
                    @foreach($parents as $cat)
                        @if($cat->id !== $category->id)
                        <option value="{{ $cat->id }}" {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" class="form-input" rows="2">{{ old('description', $category->description) }}</textarea>
            </div>
        </div>
        <div class="card-body pt-0 flex gap-3 justify-end border-t border-gray-100">
            <a href="{{ route('inventory.categories.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save</button>
        </div>
    </form>
</div>
@endsection
