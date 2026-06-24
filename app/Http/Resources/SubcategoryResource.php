<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubcategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->localized_name,
            'icon_url' => $this->icon_id ? route('icon.show', $this->icon_id) : null,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'providers_count' => (int) ($this->discoverable_profiles_count ?? 0),
        ];
    }
}
