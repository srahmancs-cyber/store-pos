<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CapitalInjection extends Model
{
    protected $fillable = [
        'amount',
        'source_type',
        'source_id',
        'destination_type',
        'purpose',
        'transaction_date',
        'created_by',
    ];

    protected $casts = [
        'amount'           => 'integer',
        'transaction_date' => 'date',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ownerTransaction(): HasOne
    {
        return $this->hasOne(OwnerTransaction::class, 'capital_injection_id');
    }
}
