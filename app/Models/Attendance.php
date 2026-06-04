<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'employee_id',
        'clock_in',
        'clock_out',
        'date',
        'duration_minutes',
    ];

    protected $casts = [
        'clock_in'         => 'datetime',
        'clock_out'        => 'datetime',
        'date'             => 'date',
        'duration_minutes' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
