<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepayment extends Model
{
    protected $fillable = [
        'loan_id',
        'amount',
        'destination_type',
        'repayment_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount'         => 'integer',
        'repayment_date' => 'date',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function loan(): BelongsTo
    {
        return $this->belongsTo(EmployeeLoan::class, 'loan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
