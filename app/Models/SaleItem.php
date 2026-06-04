<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'unit_price',
        'cost_price',
        'quantity',
        'discount_amount',
        'tax_amount',
        'total',
        'serial_number',
    ];

    protected $casts = [
        'unit_price'      => 'integer',
        'cost_price'      => 'integer',
        'quantity'        => 'integer',
        'discount_amount' => 'integer',
        'tax_amount'      => 'integer',
        'total'           => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
