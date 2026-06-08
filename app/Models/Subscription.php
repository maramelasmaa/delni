<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'starts_at',
        'ends_at',
        'is_active',
        'payment_method',
        'payment_reference',
        'payment_date',
        'notes',
        'processed_by',
        'processed_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'payment_date' => 'date',
        'is_active' => 'boolean',
        'processed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // -------------------------------------------------------------------------
    // Helpers — S7
    // -------------------------------------------------------------------------

    public function isExpired(): bool
    {
        return $this->ends_at->lt(Carbon::today());
    }

    public function isActive(): bool
    {
        return $this->is_active
            && $this->approved_at !== null
            && $this->ends_at !== null
            && $this->ends_at->gte(Carbon::today());
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }
}
