<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expense extends Model
{
    protected $fillable = [
        'category',
        'amount',
        'description',
        'date',
        'payment_method',
        'receipt_image',
        'is_recurring',
        'recurring_day_of_month',
        'parent_expense_id',
        'created_by',
    ];

    protected $casts = [
        'date'                   => 'date',
        'amount'                 => 'integer',
        'is_recurring'           => 'boolean',
        'recurring_day_of_month' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'parent_expense_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Expense::class, 'parent_expense_id');
    }
}
