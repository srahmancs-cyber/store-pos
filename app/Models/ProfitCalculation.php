<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfitCalculation extends Model
{
    protected $fillable = [
        'period_start',
        'period_end',
        'total_sales_revenue',
        'cogs',
        'total_expenses',
        'total_salaries',
        'written_off_loans',
        'donations_given',
        'other_income',
        'net_profit',
        'details_json',
        'finalised_at',
        'created_by',
    ];

    protected $casts = [
        'period_start'        => 'date',
        'period_end'          => 'date',
        'total_sales_revenue' => 'integer',
        'cogs'                => 'integer',
        'total_expenses'      => 'integer',
        'total_salaries'      => 'integer',
        'written_off_loans'   => 'integer',
        'donations_given'     => 'integer',
        'other_income'        => 'integer',
        'net_profit'          => 'integer',
        'finalised_at'        => 'datetime',
        'details_json'        => 'array',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
