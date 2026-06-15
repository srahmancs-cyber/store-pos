<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsignmentVendor extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'default_commission_rate',
        'commission_basis',
        'payout_frequency',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'default_commission_rate' => 'decimal:2',
        'is_active'               => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'consignment_vendor_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(ConsignmentPayout::class);
    }

    // -------------------------------------------------------------------------
    // Business logic helpers
    // -------------------------------------------------------------------------

    /**
     * Calculate what the store keeps for one sale item.
     * Returns [store_commission_cents, vendor_payout_cents]
     */
    public function calculateSplit(Product $product, int $salePriceCents): array
    {
        $rate   = $product->consignment_rate ?? $this->default_commission_rate;
        $basis  = $product->consignment_basis ?? $this->commission_basis;

        if ($basis === 'profit') {
            $profitCents      = $salePriceCents - $product->cost_price;
            $commissionCents  = (int) round($profitCents * $rate / 100);
        } else {
            // sale_price basis
            $commissionCents  = (int) round($salePriceCents * $rate / 100);
        }

        $commissionCents = max(0, $commissionCents);
        $vendorPayout    = $salePriceCents - $commissionCents;

        return [
            'store_commission' => $commissionCents,
            'vendor_payout'    => max(0, $vendorPayout),
        ];
    }
}
