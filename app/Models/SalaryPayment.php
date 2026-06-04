<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryPayment extends Model
{
    protected $fillable = [
        'employee_id',
        'amount',
        'period_month',
        'period_year',
        'payment_method',
        'paid_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount'       => 'integer',
        'paid_date'    => 'date',
        'period_month' => 'integer',
        'period_year'  => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
