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
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $profile = $this->route('profile');

            if (! $profile instanceof Profile) {
                $v->errors()->add('profile', 'Profile not found.');

                return;
            }

            $user = $this->user();

            if ($user === null || ! $user->is_active || $user->is_suspended) {
                $v->errors()->add('profile', 'Your account is not eligible to submit reviews.');

                return;
            }

            if (! $this->visibility->isDiscoverable($profile)) {
                $v->errors()->add('profile', 'This profile is not currently available for reviews.');

                return;
            }

            // Catch duplicate before hitting the DB unique constraint, which would
            // surface as a 500 QueryException instead of a 422 validation error.
            $alreadyReviewed = Review::withTrashed()
                ->where('profile_id', $profile->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyReviewed) {
                $v->errors()->add('profile', 'You have already submitted a review for this profile.');
            }
        });
    }
}
