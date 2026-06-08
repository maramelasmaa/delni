<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'profile_id',
        'user_id',
        'rating',
        'status',
        'is_flagged',
        'comment',
        'flagged_by',
        'flagged_at',
        'flagged_reason',
        'flag_handled_at',
        'flag_handled_by',
        'moderated_by',
        'moderated_at',
        'moderation_note',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_flagged' => 'boolean',
        'flagged_at' => 'datetime',
        'flag_handled_at' => 'datetime',
        'moderated_at' => 'datetime',
        'status' => ReviewStatus::class,
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function flaggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'flagged_by');
    }

    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function flagHandledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'flag_handled_by');
    }

    public function isApproved(): bool
    {
        return $this->status === ReviewStatus::APPROVED;
    }
}
