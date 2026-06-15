<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class SetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->numbers()
                    ->mixedCase()
                    ->symbols(),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'password.required' => __('validation.required', ['attribute' => __('validation.attributes.password')]),
            'password.confirmed' => __('validation.custom.password.confirmed'),
            'password.min' => 'يجب أن تحتوي كلمة المرور على 8 أحرف على الأقل.',
            'password.regex' => 'يجب أن تحتوي كلمة المرور على أحرف صغيرة وكبيرة وأرقام ورموز خاصة.',
        ];
    }
}
