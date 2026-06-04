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
        'cost_price',
        'selling_price',
        'current_stock',
        'reorder_point',
        'has_serial',
        'image',
        'is_active',
    ];

    protected $casts = [
        'cost_price'    => 'integer',
        'selling_price' => 'integer',
        'current_stock' => 'integer',
        'reorder_point' => 'integer',
        'has_serial'    => 'boolean',
        'is_active'     => 'boolean',
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
