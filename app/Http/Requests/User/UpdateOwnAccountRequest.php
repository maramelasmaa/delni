<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Authenticated user updating their own account info (name, email, phone).
 * This covers the User model — NOT the Profile (business) model.
 * Profile updates are handled by UpdateProfileRequest.
 *
 * Role, suspension, and security fields are stripped in prepareForValidation
 * to prevent mass-assignment even if the controller passes $request->validated().
 */
class UpdateOwnAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                // ignore() still enforces uniqueness against all OTHER users,
                // including soft-deleted ones (DB::table() bypasses SoftDeletes scope).
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[+0-9][0-9\s\-()]{6,19}$/'],
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
        ];
    }

    protected function prepareForValidation(): void
    {
        // Strip any fields a user must not self-assign.
        // Defense-in-depth: protects even if a controller uses update($request->all()).
        $this->request->remove('is_active');
        $this->request->remove('is_suspended');
        $this->request->remove('security_flagged');
        $this->request->remove('must_change_password');
        $this->request->remove('password');
    }
}
