<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action_type',
        'model_type',
        'model_id',
        'description',
        'ip_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Lifecycle hooks
    // -------------------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->created_at = $m->created_at ?? now());
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
