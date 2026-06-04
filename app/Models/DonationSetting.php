<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonationSetting extends Model
{
    protected $fillable = [
        'is_enabled',
        'percentage',
        'frequency',
        'last_calculated_date',
    ];

    protected $casts = [
        'is_enabled'           => 'boolean',
        'percentage'           => 'decimal:2',
        'last_calculated_date' => 'date',
    ];
}
