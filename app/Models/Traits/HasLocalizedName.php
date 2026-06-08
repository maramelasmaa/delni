<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Support\Facades\App;

trait HasLocalizedName
{
    public function getLocalizedNameAttribute(): string
    {
        if (App::getLocale() === 'ar' && ! empty($this->name_ar)) {
            return $this->name_ar;
        }

        return $this->name;
    }
}
