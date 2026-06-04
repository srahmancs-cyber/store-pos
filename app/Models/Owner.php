<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Owner extends Model
{
    protected $fillable = [
        'name',
        'sort_order',
        'profit_share_percentage',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'profit_share_percentage' => 'decimal:2',
        'is_active'               => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function transactions(): HasMany
    {
        return $this->hasMany(OwnerTransaction::class);
    }
}
