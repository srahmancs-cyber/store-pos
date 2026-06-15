<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'category_id',
        'supplier_id',
        'consignment_vendor_id',
        'consignment_rate',
        'consignment_basis',
        'is_consignment',
        'investor_id',
        'cost_price',
        'selling_price',
        'current_stock',
        'reorder_point',
        'has_serial',
        'image',
        'is_active',
    ];

    protected $casts = [
        'cost_price'              => 'integer',
        'selling_price'           => 'integer',
        'current_stock'           => 'integer',
        'reorder_point'           => 'integer',
        'has_serial'              => 'boolean',
        'is_active'               => 'boolean',
        'is_consignment'          => 'boolean',
        'consignment_rate'        => 'decimal:2',
    ];

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getFormattedCostPriceAttribute(): string
    {
        return number_format($this->cost_price / 100, 2);
    }

    public function getFormattedSellingPriceAttribute(): string
    {
        return number_format($this->selling_price / 100, 2);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function consignmentVendor(): BelongsTo
    {
        return $this->belongsTo(ConsignmentVendor::class, 'consignment_vendor_id');
    }

    public function investor(): BelongsTo
    {
        return $this->belongsTo(Owner::class, 'investor_id');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
