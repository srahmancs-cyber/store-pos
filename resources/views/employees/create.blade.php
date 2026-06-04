@extends('layouts.app')
@section('title', 'Add Employee')
@section('breadcrumb')
    <a href="{{ route('employees.index') }}" class="text-gray-500 hover:text-gray-900">Employees</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Add Employee</span>
@endsection
@section('content')
<div class="pt-6 max-w-xl">
    <h1 class="mb-6">Add Employee</h1>
    <form method="POST" action="{{ route('employees.store') }}" class="space-y-5">
        @csrf
        <div class="card">
            <div class="card-header"><h3>Personal Information</h3></div>
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input @error('name') border-red-400 @enderror"
                        value="{{ old('name') }}" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" class="form-input @error('email') border-red-400 @enderror"
                            value="{{ old('email') }}" required>
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-input" value="{{ old('phone') }}">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Role <span class="text-red-500">*</span></label>
                        <select name="role" class="form-select" required>
                            @foreach(['cashier'=>'Cashier','manager'=>'Manager','admin'=>'Admin'] as $val=>$label)
                            <option value="{{ $val }}" {{ old('role')===$val?'selected':'' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Hire Date</label>
                        <input type="date" name="hire_date" class="form-input" value="{{ old('hire_date') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Salary</h3></div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Salary Type <span class="text-red-500">*</span></label>
                        <select name="salary_type" class="form-select" required>
                            <option value="fixed" {{ old('salary_type')==='fixed'?'selected':'' }}>Fixed Monthly</option>
                            <option value="hourly" {{ old('salary_type')==='hourly'?'selected':'' }}>Hourly</option>
                            <option value="commission" {{ old('salary_type')==='commission'?'selected':'' }}>Commission %</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Amount / Rate <span class="text-red-500">*</span></label>
                        <input type="number" name="salary_value" class="form-input @error('salary_value') border-red-400 @enderror"
                            value="{{ old('salary_value','0') }}" min="0" step="0.01" required>
                        @error('salary_value')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info text-xs">
            <i data-lucide="info" class="w-4 h-4 flex-shrink-0"></i>
            A temporary password will be auto-generated. Share it with the employee so they can log in.
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('employees.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create Employee</button>
        </div>
    </form>
</div>
@endsection
