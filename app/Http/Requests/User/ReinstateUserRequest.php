<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReinstateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reinstate', $this->route('user'));
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'reinstatement_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $user = $this->route('user');

            if (! $user instanceof User) {
                return;
            }

            if (! $user->is_suspended) {
                $v->errors()->add('user', 'This user is not currently suspended.');
            }
        });
    }
}
