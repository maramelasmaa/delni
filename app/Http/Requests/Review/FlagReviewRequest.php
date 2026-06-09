<?php

declare(strict_types=1);

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Authenticated user flagging a review as inappropriate.
 *
 * Authorization is delegated to ReviewPolicy::flag() which enforces:
 *   - Cannot flag own review
 *   - Review must be on a discoverable profile
 *   - Providers can only flag reviews on their own profile
 *   - Public users can flag any review on any visible profile
 *
 * Eligibility checks (is_active, is_suspended) are validated in withValidator()
 * to provide a 422 validation error instead of a 403 authorization error — better UX.
 */
class FlagReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->is_active
            && $user->can('flag', $this->route('review'));
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $user = $this->user();

            if ($user && $user->is_suspended) {
                $v->errors()->add('reason', __('messages.account_not_eligible_flag'));
            }
        });
    }
}
