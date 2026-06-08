<?php

declare(strict_types=1);

namespace App\Http\Requests\Review;

use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Admin approves or rejects a review.
 * Authorization delegated to ReviewPolicy::moderate() which is admin-only.
 *
 * Validation ensures status cannot be set to 'pending' via this action —
 * 'pending' is only a valid initial state, never a target state from moderation.
 */
class ModerateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('moderate', $this->route('review'));
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'moderation_note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $review = $this->route('review');

            if (! $review instanceof Review) {
                return;
            }

            // Prevent re-moderating with the same status — idempotency guard for UX.
            // The underlying update is not harmful (ReviewObserver::updated() only
            // fires wasChanged('status')), but this prevents accidental duplicate actions.
            if ($review->status === $this->string('status')->toString()) {
                $v->errors()->add(
                    'status',
                    "This review is already {$review->status}.",
                );
            }
        });
    }
}
