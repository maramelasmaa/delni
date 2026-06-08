<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'title',
        'issuer',
        'verification_url',
        'issue_date',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
