<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsignmentPayout extends Model
{
    protected $fillable = [
        'consignment_vendor_id',
        'period_start',
        'period_end',
        'total_sales_amount',
        'store_commission_amount',
        'vendor_payout_amount',
        'items_sold',
        'status',
        'paid_date',
        'payment_method',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'period_start'            => 'date',
        'period_end'              => 'date',
        'paid_date'               => 'date',
        'total_sales_amount'      => 'integer',
        'store_commission_amount' => 'integer',
        'vendor_payout_amount'    => 'integer',
        'items_sold'              => 'integer',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(ConsignmentVendor::class, 'consignment_vendor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
