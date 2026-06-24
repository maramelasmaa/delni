<?php

namespace App\Http\Requests;

use App\Support\IconSourceUrlValidator;
use Illuminate\Foundation\Http\FormRequest;

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
                    try {
                        app(IconSourceUrlValidator::class)->probe((string) $value);
                    } catch (\InvalidArgumentException $e) {
                        $fail($e->getMessage());
                    }
                },
            ],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
        ];
    }
}
