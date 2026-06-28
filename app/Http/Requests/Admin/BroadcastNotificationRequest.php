<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BroadcastNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
            'data' => ['nullable', 'array'],
            'data.url' => ['nullable', 'string', 'max:255'],
            'data.pathname' => ['nullable', 'string', 'max:255'],
            'data.provider_slug' => ['nullable', 'string', 'max:120'],
            'data.category_slug' => ['nullable', 'string', 'max:120'],
            'data.subcategory_slug' => ['nullable', 'string', 'max:120'],
        ];
    }
}
