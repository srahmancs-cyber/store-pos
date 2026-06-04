@extends('layouts.app')
@section('title', 'Edit Employee')
@section('breadcrumb')
    <a href="{{ route('employees.index') }}" class="text-gray-500 hover:text-gray-900">Employees</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Edit</span>
@endsection
@section('content')
<div class="pt-6 max-w-xl">
    <h1 class="mb-6">Edit Employee</h1>
    <form method="POST" action="{{ route('employees.update', $employee) }}" class="space-y-5">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-header"><h3>Personal Information</h3></div>
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $employee->name) }}" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" class="form-input @error('email') border-red-400 @enderror"
                            value="{{ old('email', $employee->email) }}" required>
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-input" value="{{ old('phone', $employee->phone) }}">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            @foreach(['cashier'=>'Cashier','manager'=>'Manager','admin'=>'Admin'] as $val=>$label)
                            <option value="{{ $val }}" {{ old('role',$employee->role)===$val?'selected':'' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Hire Date</label>
                        <input type="date" name="hire_date" class="form-input"
                            value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active', $employee->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
                    <label for="is_active" class="text-sm">Active</label>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3>Salary</h3></div>
            <div class="card-body grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Salary Type</label>
                    <select name="salary_type" class="form-select">
                        <option value="fixed" {{ old('salary_type',$employee->salary_type)==='fixed'?'selected':'' }}>Fixed Monthly</option>
                        <option value="hourly" {{ old('salary_type',$employee->salary_type)==='hourly'?'selected':'' }}>Hourly</option>
                        <option value="commission" {{ old('salary_type',$employee->salary_type)==='commission'?'selected':'' }}>Commission %</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Amount / Rate</label>
                    <input type="number" name="salary_value" class="form-input"
                        value="{{ old('salary_value', number_format($employee->salary_value/100,2,'.','')) }}"
                        min="0" step="0.01">
                </div>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="{{ route('employees.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
