<?php

namespace App\Models;

use App\Models\Traits\HasLocalizedName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory, HasLocalizedName;

    protected $fillable = [
        'name',
        'name_ar',
        'duration_months',
        'price_lyd',
        'is_active',
        'tier',
        'featured_days_per_subscription',
        'includes_homepage_featured',
        'includes_top_search',
        'includes_category_spotlight',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_lyd' => 'decimal:2',
        'includes_homepage_featured' => 'boolean',
        'includes_top_search' => 'boolean',
        'includes_category_spotlight' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}
