<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $fillable = [
        'amount',
        'calculated_from_profit',
        'period_start',
        'period_end',
        'recipient',
        'status',
        'given_date',
        'notes',
    ];

    protected $casts = [
        'amount'                 => 'integer',
        'calculated_from_profit' => 'integer',
        'period_start'           => 'date',
        'period_end'             => 'date',
        'given_date'             => 'date',
    ];
}
