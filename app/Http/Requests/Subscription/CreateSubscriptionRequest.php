<?php

declare(strict_types=1);

namespace App\Http\Requests\Subscription;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Admin creates a subscription for a provider.
 *
 * Intentionally omits overlap validation — that invariant is enforced by
 * SubscriptionValidationService::validateDates() called from
 * SubscriptionObserver::creating() with a lockForUpdate transaction.
 * Duplicating it here would create two enforcement points that can diverge.
 *
 * The subscription_plans table has no 'amount' column — price is stored on
 * the plan (price_lyd). See CB-3 in the architecture audit.
 */
class CreateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Subscription::class);
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->whereNull('deleted_at'),
                $this->userMustBeProvider(),
            ],
            'plan_id' => [
                'required',
                Rule::exists('subscription_plans', 'id')->where('is_active', true),
            ],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function userMustBeProvider(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $user = User::find($value);

            if (! $user || ! $user->hasRole('provider')) {
                $fail('Subscriptions can only be created for provider accounts.');
            }
        };
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'The selected user does not exist.',
            'plan_id.exists' => 'The selected plan is not available.',
            'ends_at.after' => 'The subscription end date must be after the start date.',
        ];
    }
}
