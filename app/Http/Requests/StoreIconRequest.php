<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;

class StoreIconRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:icons'],
            'url' => [
                'required',
                'url',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if (! $this->isValidIconUrl($value)) {
                        $fail('URL must point to a valid SVG or PNG icon.');
                    }
                },
            ],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
        ];
    }

    private function isValidIconUrl(string $url): bool
    {
        try {
            $response = Http::timeout(5)->head($url);
            $contentType = $response->header('content-type');

            return in_array($contentType, ['image/svg+xml', 'image/png']);
        } catch (\Throwable) {
            return false;
        }
    }
}
