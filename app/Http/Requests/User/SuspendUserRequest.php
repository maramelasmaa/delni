<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SuspendUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('suspend', $this->route('user'));
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'suspension_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $user = $this->route('user');

            if (! $user instanceof User) {
                return;
            }

            if ($user->is_suspended) {
                $v->errors()->add('user', 'This user is already suspended.');
            }

            if ($this->user()->id === $user->id) {
                $v->errors()->add('user', 'You cannot suspend your own account.');
            }
        });
    }
}
