@extends('layouts.app')
@section('title', 'Categories')
@section('breadcrumb')
    <span class="text-gray-500">Inventory</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="text-gray-900 font-medium">Categories</span>
@endsection

@section('content')
<div class="pt-6 space-y-4" x-data="{ showCreate: false, editId: null }">
    <div class="flex items-center justify-between">
        <h1>Categories</h1>
        <button @click="showCreate = true" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Category
        </button>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Name</th>
                    <th>Parent</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td class="font-medium">{{ $cat->name }}</td>
                        <td class="text-gray-500">{{ $cat->parent?->name ?? '—' }}</td>
                        <td class="text-gray-500 max-w-xs truncate">{{ $cat->description ?? '—' }}</td>
                        <td>{{ $cat->products_count ?? 0 }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('inventory.categories.edit', $cat) }}" class="btn btn-secondary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('inventory.categories.destroy', $cat) }}"
                                    onsubmit="return confirm('Delete {{ $cat->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-gray-400 py-8">No categories yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $categories->links() }}</div>
        @endif
    </div>

    {{-- Create Modal --}}
    <div x-show="showCreate" x-cloak class="modal-backdrop" @click.self="showCreate=false">
        <div class="modal">
            <div class="modal-header">
                <h3>Add Category</h3>
                <button @click="showCreate=false" class="text-gray-400 hover:text-gray-700">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('inventory.categories.store') }}">
                @csrf
                <div class="modal-body space-y-4">
                    <div>
                        <label class="form-label">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="form-input" required value="{{ old('name') }}">
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Parent Category</label>
                        <select name="parent_id" class="form-select">
                            <option value="">— None (top-level) —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input" rows="2">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="showCreate=false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
