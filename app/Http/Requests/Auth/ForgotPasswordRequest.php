<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Intentionally does not validate email existence to prevent user enumeration.
 * The controller must respond identically whether the email exists or not.
 */
class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc'],
        ];
    }
}
