<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLog extends Model
{
    public $timestamps = false;

    const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'adjustment_type',
        'quantity',
        'old_quantity',
        'new_quantity',
        'reason',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'old_quantity' => 'integer',
        'new_quantity' => 'integer',
        'created_at'   => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Lifecycle hooks
    // -------------------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->created_at = now());
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
