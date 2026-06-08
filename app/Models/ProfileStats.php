<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileStats extends Model
{
    use HasFactory;

    protected $table = 'profile_stats';

    protected $primaryKey = 'profile_id';

    public $incrementing = false;

    protected $fillable = [
        'profile_id',
        'rating_avg',
        'reviews_count',
        'is_top_rated',
        'is_featured',
        'featured_until',
        'is_homepage_featured',
        'homepage_featured_until',
        'is_top_search',
        'top_search_until',
        'is_top_category',
        'top_category_until',
        'is_top_subcategory',
        'top_subcategory_until',
    ];

    protected $casts = [
        'rating_avg' => 'decimal:1',
        'is_top_rated' => 'boolean',
        'is_featured' => 'boolean',
        'featured_until' => 'date',
        'is_homepage_featured' => 'boolean',
        'homepage_featured_until' => 'date',
        'is_top_search' => 'boolean',
        'top_search_until' => 'date',
        'is_top_category' => 'boolean',
        'top_category_until' => 'date',
        'is_top_subcategory' => 'boolean',
        'top_subcategory_until' => 'date',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function isFeaturedActive(): bool
    {
        return $this->is_featured
            && $this->featured_until !== null
            && $this->featured_until->isFuture();
    }
}
