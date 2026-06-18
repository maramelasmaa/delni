<?php

namespace App\Models;

use App\Models\Traits\HasLocalizedName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcategory extends Model
{
    use HasFactory, HasLocalizedName, SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'name_ar', 'search_name', 'slug', 'icon_id', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function icon(): BelongsTo
    {
        return $this->belongsTo(Icon::class);
    }

    public function getIconAttribute()
    {
        if ($this->relationLoaded('icon')) {
            return $this->getRelationValue('icon');
        }

        return $this->icon()->getResults();
    }

    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class, 'profile_subcategory', 'subcategory_id', 'profile_id')->withTimestamps();
    }
}
