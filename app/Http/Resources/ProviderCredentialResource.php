<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderCredentialResource extends JsonResource
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
            'title' => $this->title,
            'issuer' => $this->issuer,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'verification_url' => $this->verification_url,
            'notes' => $this->notes,
        ];
    }
}
