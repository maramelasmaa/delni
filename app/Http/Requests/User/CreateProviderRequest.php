<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Admin creates a provider account. Role is always 'provider' and is
 * forced by the controller — it is not a user-supplied field.
 *
 * Password is nullable: if omitted the controller should auto-generate
 * a secure password.
 */
class CreateProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('createProvider', User::class);
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[+0-9][0-9\s\-()]{6,19}$/'],
            'password' => [
                'nullable',
                Password::min(8)->letters()->numbers()->mixedCase()->symbols(),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.unique' => __('validation.custom.email.unique'),
            'phone.regex' => __('validation.custom.phone.regex'),
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

    protected function prepareForValidation(): void
    {
        // Role is never user-supplied — strip to prevent injection into validated().
        $this->request->remove('role');
        $this->request->remove('is_active');
        $this->request->remove('is_suspended');
        $this->request->remove('security_flagged');
    }
}
