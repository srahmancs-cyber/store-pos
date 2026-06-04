@extends('layouts.app')
@section('title', 'Attendance')
@section('breadcrumb')
    <span class="text-gray-500">Employees</span>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-300"></i>
    <span class="font-medium">Attendance</span>
@endsection
@section('content')
<div class="pt-6 space-y-6">
    <h1>Attendance</h1>

    <div class="card">
        <div class="card-header"><h3>Today — {{ now()->format('F d, Y') }}</h3></div>
        <div class="table-wrapper">
            <table class="table">
                <thead><tr>
                    <th>Employee</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Clock In</th>
                    <th>Today's Hours</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @foreach($employees as $row)
                    @php
                        $emp         = $row['employee'];
                        $isClockedIn = $row['clocked_in'];
                        $hoursToday  = $row['hours_today'];
                        $record      = $row['attendance'];
                    @endphp
                    <tr>
                        <td class="font-medium">{{ $emp->name }}</td>
                        <td class="capitalize text-gray-500">{{ $emp->role }}</td>
                        <td>
                            @if($isClockedIn)
                                <span class="badge badge-green">Clocked In</span>
                            @else
                                <span class="badge badge-gray">Clocked Out</span>
                            @endif
                        </td>
                        <td class="text-gray-500">
                            {{ $record?->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('H:i') : '—' }}
                        </td>
                        <td>
                            @if($hoursToday > 0)
                                {{ floor($hoursToday) }}h {{ round(($hoursToday - floor($hoursToday)) * 60) }}m
                            @else —
                            @endif
                        </td>
                        <td>
                            @if($isClockedIn)
                                <form method="POST" action="{{ route('employees.attendance.clock-out') }}">
                                    @csrf
                                    <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                                    <button class="btn btn-secondary btn-sm">Clock Out</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('employees.attendance.clock-in') }}">
                                    @csrf
                                    <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                                    <button class="btn btn-primary btn-sm">Clock In</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
