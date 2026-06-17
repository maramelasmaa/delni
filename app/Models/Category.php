<?php

namespace App\Models;

use App\Models\Traits\HasLocalizedName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, HasLocalizedName, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'icon_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class)->orderBy('sort_order');
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
