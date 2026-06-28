<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterPushTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'token' => [
                'required',
                'string',
                'max:255',
                'regex:/^(ExponentPushToken|ExpoPushToken)\[[A-Za-z0-9\-_]+\]$/',
            ],
            'provider' => ['required', 'string', Rule::in(['expo'])],
            'platform' => ['required', 'string', Rule::in(['android', 'ios'])],
            'device_name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
