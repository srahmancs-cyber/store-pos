<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerTransaction extends Model
{
    protected $fillable = [
        'owner_id',
        'type',
        'amount',
        'transaction_date',
        'notes',
        'capital_injection_id',
        'created_by',
    ];

    protected $casts = [
        'amount'           => 'integer',
        'transaction_date' => 'date',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
