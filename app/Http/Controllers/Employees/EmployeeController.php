<?php

namespace App\Http\Controllers\Employees;

use App\Helpers\Money;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('user');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $employees = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|max:255|unique:employees,email|unique:users,email',
            'phone'        => 'nullable|string|max:50',
            'role'         => 'required|in:admin,manager,cashier',
            'hire_date'    => 'nullable|date',
            'salary_type'  => 'required|in:fixed,hourly,commission',
            'salary_value' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $temporaryPassword = Str::random(12);

            // Create User account
            $user = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($temporaryPassword),
                'role'      => $request->role,
                'is_active' => true,
                'phone'     => $request->phone,
                'hire_date' => $request->hire_date,
            ]);

            // Create Employee record
            $employee = Employee::create([
                'user_id'      => $user->id,
                'name'         => $request->name,
                'email'        => $request->email,
                'phone'        => $request->phone,
                'role'         => $request->role,
                'hire_date'    => $request->hire_date,
                'salary_type'  => $request->salary_type,
                'salary_value' => Money::toCents($request->salary_value),
                'is_active'    => true,
            ]);

            // Flash the temp password so admin can share it
            session(['temp_password_' . $employee->id => $temporaryPassword]);

            ActivityLogger::log('employee_create', "Employee '{$employee->name}' created (role: {$employee->role})", Employee::class, $employee->id);
        });

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'attendance', 'salaryPayments', 'loans']);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|max:255|unique:employees,email,' . $employee->id,
            'phone'        => 'nullable|string|max:50',
            'role'         => 'required|in:admin,manager,cashier',
            'hire_date'    => 'nullable|date',
            'salary_type'  => 'required|in:fixed,hourly,commission',
            'salary_value' => 'required|numeric|min:0',
            'is_active'    => 'boolean',
        ]);

        DB::transaction(function () use ($request, $employee) {
            $employee->update([
                'name'         => $request->name,
                'email'        => $request->email,
                'phone'        => $request->phone,
                'role'         => $request->role,
                'hire_date'    => $request->hire_date,
                'salary_type'  => $request->salary_type,
                'salary_value' => Money::toCents($request->salary_value),
                'is_active'    => $request->boolean('is_active'),
            ]);

            // Sync User account
            if ($employee->user) {
                $employee->user->update([
                    'name'      => $request->name,
                    'email'     => $request->email,
                    'role'      => $request->role,
                    'phone'     => $request->phone,
                    'hire_date' => $request->hire_date,
                    'is_active' => $request->boolean('is_active'),
                ]);
            }

            ActivityLogger::log('employee_update', "Employee '{$employee->name}' updated", Employee::class, $employee->id);
        });

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->salaryPayments()->exists() || $employee->loans()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete employee with salary or loan records.']);
        }

        ActivityLogger::log('employee_delete', "Employee '{$employee->name}' deleted", Employee::class, $employee->id);

        DB::transaction(function () use ($employee) {
            $userId = $employee->user_id;
            $employee->delete();

            if ($userId) {
                User::where('id', $userId)->update(['is_active' => false]);
            }
        });

        return redirect()->route('employees.index')->with('success', 'Employee removed.');
    }
}
