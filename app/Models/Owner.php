<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    // -------------------------------------------------------------------------
    // For investors: dynamically computed contribution from linked products
    // -------------------------------------------------------------------------

    /**
     * Returns the total cost value of all products funded by this investor.
     * Formula: SUM(cost_price * current_stock) for products where investor_id = this->id
     * This is the live "invested capital in stock" figure.
     */
    public function getComputedContributionAttribute(): int
    {
        if ($this->type !== 'investor') {
            return (int) $this->contribution_amount;
        }

        return (int) \App\Models\Product::where('investor_id', $this->id)
            ->selectRaw('SUM(cost_price * current_stock) as total')
            ->value('total') ?? 0;
    }

    public function investedProducts(): HasMany
    {
        return $this->hasMany(\App\Models\Product::class, 'investor_id');
    }
}
