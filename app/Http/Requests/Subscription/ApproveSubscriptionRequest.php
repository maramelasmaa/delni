<?php

declare(strict_types=1);

namespace App\Http\Requests\Subscription;

use App\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Admin approves a pending subscription.
 * No form body is required — the subscription is identified by the route parameter.
 * Validation ensures idempotency: approving an already-active subscription is rejected.
 */
class ApproveSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('approve', $this->route('subscription'));
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $subscription = $this->route('subscription');

            if (! $subscription instanceof Subscription) {
                $v->errors()->add('subscription', 'Subscription not found.');

                return;
            }

            if ($subscription->is_active) {
                $v->errors()->add('subscription', 'This subscription is already approved and active.');
            }
        });
    }
}
