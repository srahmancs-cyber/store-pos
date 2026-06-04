<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeLoan extends Model
{
    protected $fillable = [
        'employee_id',
        'amount',
        'remaining_balance',
        'reason',
        'source_type',
        'status',
        'auto_deduct',
        'auto_deduct_amount',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount'             => 'integer',
        'remaining_balance'  => 'integer',
        'auto_deduct_amount' => 'integer',
        'auto_deduct'        => 'boolean',
        'approved_at'        => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class, 'loan_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
