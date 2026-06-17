<?php

declare(strict_types=1);

namespace App\Http\Requests\Review;

use App\Models\Profile;
use App\Models\Review;
use App\Services\ProfileVisibilityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Authenticated user submitting a review for a provider profile.
 *
 * Authorization is delegated to ReviewPolicy::create() which enforces:
 *   - R2: Cannot review own profile (profile->user_id !== auth user)
 *   - R1: Profile must be publicly discoverable
 *
 * Additional validation here:
 *   - Profile visibility confirmed with a validation error (better UX than policy 403)
 *   - Duplicate review detected early with a clear message (DB constraint catches it
 *     anyway, but a raw QueryException produces a 500, not a validation error)
 *
 * Reviews are intentionally live by default for the marketplace flow. The
 * controller still sets status explicitly instead of relying on a DB default.
 *
 * Security: This request enforces all eligibility checks via middleware and policy.
 * Eligibility middleware (EnsureReviewEligible) on the route ensures:
 *   - User has not exceeded 10 reviews/day limit
 *
 * @see EnsureReviewEligible middleware
 */
class CreateReviewRequest extends FormRequest
{
    public function __construct(
        private readonly ProfileVisibilityService $visibility,
    ) {
        parent::__construct();
    }

    public function authorize(): bool
    {
        $profile = $this->route('profile');

        return $this->user() !== null
            && $this->user()->can('create', [Review::class, $profile]);
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if (empty($this->rating) && empty($this->comment)) {
                $v->errors()->add('rating', 'يجب إدخال تقييم بالنجوم أو كتابة تعليق لإرسال المراجعة.');

                return;
            }

            $profile = $this->route('profile');

            if (! $profile instanceof Profile) {
                $v->errors()->add('profile', __('messages.profile_not_found'));

                return;
            }

            $user = $this->user();

            if ($user === null || ! $user->is_active || $user->is_suspended) {
                $v->errors()->add('profile', __('messages.account_not_eligible_review'));

                return;
            }

            if (! $this->visibility->isDiscoverable($profile)) {
                $v->errors()->add('profile', __('messages.profile_not_discoverable'));

                return;
            }

            // Catch duplicate before hitting the DB unique constraint, which would
            // surface as a 500 QueryException instead of a 422 validation error.
            $alreadyReviewed = Review::withTrashed()
                ->where('profile_id', $profile->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyReviewed) {
                $v->errors()->add('profile', __('messages.already_reviewed'));
            }
        });
    }
}
