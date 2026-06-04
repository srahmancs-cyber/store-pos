<?php

namespace App\Http\Controllers\Employees;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        $employees = Employee::where('is_active', true)
            ->with(['attendance' => fn ($q) => $q->whereDate('date', $date)])
            ->orderBy('name')
            ->get()
            ->map(function (Employee $employee) use ($date) {
                $record = $employee->attendance->first();

                return [
                    'employee'        => $employee,
                    'attendance'      => $record,
                    'clocked_in'      => $record && !$record->clock_out,
                    'clocked_out'     => $record && $record->clock_out,
                    'hours_today'     => $record ? round(($record->duration_minutes ?? 0) / 60, 2) : 0,
                ];
            });

        // For history tab — paginated attendance
        $history = Attendance::with('employee')
            ->when($request->filled('employee_id'), fn ($q) => $q->where('employee_id', $request->employee_id))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('date', '<=', $request->date_to))
            ->latest('date')
            ->paginate(30)
            ->withQueryString();

        $allEmployees = Employee::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('employees.attendance', compact('employees', 'date', 'history', 'allEmployees'));
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'nullable|integer|exists:employees,id',
        ]);

        // Resolve employee — by explicit ID or logged-in user
        if ($request->filled('employee_id')) {
            $employee = Employee::findOrFail($request->employee_id);
        } else {
            $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        }

        $today = Carbon::today();

        // Check not already clocked in
        $existing = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->whereNull('clock_out')
            ->exists();

        if ($existing) {
            $message = "Employee '{$employee->name}' is already clocked in today.";
            if ($request->wantsJson()) {
                return response()->json(['error' => $message], 422);
            }
            return back()->withErrors(['error' => $message]);
        }

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date'        => $today,
            'clock_in'    => now(),
            'clock_out'   => null,
        ]);

        ActivityLogger::log('clock_in', "Employee '{$employee->name}' clocked in", Attendance::class, $attendance->id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'attendance' => $attendance]);
        }

        return back()->with('success', "Clock-in recorded for {$employee->name}.");
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'employee_id' => 'nullable|integer|exists:employees,id',
        ]);

        if ($request->filled('employee_id')) {
            $employee = Employee::findOrFail($request->employee_id);
        } else {
            $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        }

        $today = Carbon::today();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();

        if (!$attendance) {
            $message = "No open clock-in found for '{$employee->name}' today.";
            if ($request->wantsJson()) {
                return response()->json(['error' => $message], 422);
            }
            return back()->withErrors(['error' => $message]);
        }

        $clockOut        = now();
        $durationMinutes = (int) $attendance->clock_in->diffInMinutes($clockOut);

        $attendance->update([
            'clock_out'        => $clockOut,
            'duration_minutes' => $durationMinutes,
        ]);

        ActivityLogger::log('clock_out', "Employee '{$employee->name}' clocked out ({$durationMinutes} min)", Attendance::class, $attendance->id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'duration_minutes' => $durationMinutes]);
        }

        return back()->with('success', "Clock-out recorded for {$employee->name}. Duration: {$durationMinutes} minutes.");
    }
}
