<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Owner extends Model
{
    protected $fillable = [
        'name',
        'type',
        'sort_order',
        'profit_share_percentage',
        'is_active',
        'notes',
        'contribution_amount',
        'agreement_start_date',
        'agreement_end_date',
        'profit_basis',
    ];

    protected $casts = [
        'profit_share_percentage' => 'decimal:2',
        'is_active'               => 'boolean',
        'contribution_amount'     => 'integer',
        'agreement_start_date'    => 'date',
        'agreement_end_date'      => 'date',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function transactions(): HasMany
    {
        return $this->hasMany(OwnerTransaction::class);
    }
}
