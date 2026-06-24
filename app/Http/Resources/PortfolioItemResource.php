<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrls = [];
        if ($this->relationLoaded('images')) {
            $imageUrls = $this->images->map(function ($img) {
                return $img->path ? asset('storage/'.$img->path) : null;
            })->filter()->values()->all();
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description ?: $this->short_description,
            'link' => $this->link ?: $this->main_url,
            'images' => $imageUrls,
        ];
    }
}
