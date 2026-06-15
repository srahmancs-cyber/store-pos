@extends('layouts.app')
@section('title', 'Import Preview')
@section('breadcrumb')
    <a href="{{ route('inventory.products.index') }}" class="text-gray-500 hover:text-gray-900">Products</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <a href="{{ route('inventory.products.import') }}" class="text-gray-500 hover:text-gray-900">Import</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Preview</span>
@endsection

@section('content')
@php
    $validCount   = count(array_filter($rows, fn($r) => empty($r['errors'])));
    $errorCount   = count(array_filter($rows, fn($r) => !empty($r['errors'])));
    $createCount  = count(array_filter($rows, fn($r) => empty($r['errors']) && $r['action'] === 'create'));
    $updateCount  = count(array_filter($rows, fn($r) => empty($r['errors']) && $r['action'] === 'update'));
@endphp

<div class="pt-6 space-y-5">

    <div class="flex items-center justify-between">
        <h1>Import Preview</h1>
        <a href="{{ route('inventory.products.import') }}" class="btn-secondary">
            <i data-lucide="upload" class="w-4 h-4"></i> Upload Different File
        </a>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="stat-label">Total Rows</p>
            <p class="stat-value">{{ count($rows) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Will Create</p>
            <p class="stat-value text-green-700">{{ $createCount }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Will Update</p>
            <p class="stat-value text-blue-700">{{ $updateCount }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Rows with Errors</p>
            <p class="stat-value {{ $errorCount > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $errorCount }}</p>
        </div>
    </div>

    @if($errorCount > 0)
    <div class="alert alert-warning">
        <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0"></i>
        <div>
            <strong>{{ $errorCount }} row(s) have errors</strong> and will be skipped.
            Fix the errors in your CSV and re-upload to import those rows.
            Rows without errors ({{ $validCount }}) will still be imported.
        </div>
    </div>
    @endif

    @if($validCount === 0)
    <div class="alert alert-danger">
        <i data-lucide="x-circle" class="w-4 h-4 flex-shrink-0"></i>
        All rows have errors. Please fix your CSV file and try again.
    </div>
    @endif

    {{-- Preview Table --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h3>Row Preview</h3>
            <span class="text-xs text-gray-400">Rows with errors are highlighted red and will be skipped</span>
        </div>
        <div class="table-wrapper">
            <table class="table text-xs">
                <thead><tr>
                    <th>Row</th>
                    <th>Action</th>
                    <th>Name</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Cost</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                </tr></thead>
                <tbody>
                    @foreach($rows as $row)
                    <tr class="{{ !empty($row['errors']) ? 'bg-red-50' : '' }}">
                        <td class="text-gray-400">{{ $row['line'] }}</td>
                        <td>
                            @if(!empty($row['errors']))
                                <span class="badge badge-red">Error</span>
                            @elseif($row['action'] === 'create')
                                <span class="badge badge-green">Create</span>
                            @else
                                <span class="badge badge-blue">Update</span>
                            @endif
                        </td>
                        <td class="font-medium max-w-32 truncate">{{ $row['data']['name'] }}</td>
                        <td class="font-mono">{{ $row['data']['sku'] }}</td>
                        <td class="text-gray-500">{{ $row['data']['category'] ?: '—' }}</td>
                        <td>{{ $sym }}{{ number_format((float)$row['data']['cost_price'], 2) }}</td>
                        <td>{{ $sym }}{{ number_format((float)$row['data']['selling_price'], 2) }}</td>
                        <td>{{ $row['data']['current_stock'] }}</td>
                        <td>
                            @if(!empty($row['errors']))
                                <div class="space-y-0.5">
                                    @foreach($row['errors'] as $err)
                                    <p class="text-red-600 text-xs">• {{ $err }}</p>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-green-600">✓ Valid</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Actions --}}
    @if($validCount > 0)
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">
            Confirming will import <strong>{{ $validCount }} valid row(s)</strong>
            ({{ $createCount }} created, {{ $updateCount }} updated).
            @if($errorCount > 0)
            <span class="text-red-500">{{ $errorCount }} row(s) with errors will be skipped.</span>
            @endif
        </p>
        <div class="flex gap-3">
            <a href="{{ route('inventory.products.import') }}" class="btn-secondary">Cancel</a>
            <form method="POST" action="{{ route('inventory.products.import.commit') }}">
                @csrf
                <button type="submit" class="btn-primary">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Confirm Import ({{ $validCount }} rows)
                </button>
            </form>
        </div>
    </div>
    @else
    <div class="flex justify-end">
        <a href="{{ route('inventory.products.import') }}" class="btn-primary">Try Again</a>
    </div>
    @endif

</div>
@endsection
