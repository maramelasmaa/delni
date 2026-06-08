<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Public user self-registration (role: 'user').
 * Providers are created by admins via CreateProviderRequest.
 *
 * Note: Rule::unique() uses DB::table() internally, bypassing Eloquent's
 * SoftDeletes global scope — soft-deleted users ARE included in the check.
 */
class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[+0-9][0-9\s\-()]{6,19}$/'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->numbers()
                    ->mixedCase()
                    ->symbols()
                    ->uncompromised(),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.unique' => __('validation.custom.email.unique'),
            'phone.regex' => __('validation.custom.phone.regex'),
            'password.confirmed' => __('validation.custom.password.confirmed'),
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => __('validation.attributes.name'),
            'email' => __('validation.attributes.email'),
            'phone' => __('validation.attributes.phone'),
            'password' => __('validation.attributes.password'),
        ];
    }
}
