<?php

declare(strict_types=1);

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Admin updates non-financial subscription fields.
 *
 * Financial fields (user_id, plan_id, starts_at, ends_at) are immutable
 * and enforced by SubscriptionObserver::updating(). This request strips
 * those fields in prepareForValidation so they cannot be submitted at all,
 * providing a clear error boundary at the HTTP layer before the observer runs.
 *
 * 'is_active' is also stripped — use ApproveSubscriptionRequest for that.
 */
class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('subscription'));
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'payment_date' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Financial fields are immutable per SubscriptionObserver::updating().
        // Strip them at the HTTP boundary so the observer never even sees them.
        // This also produces a cleaner validation response (extra fields are silently
        // ignored by validated() anyway, but explicit removal prevents confusion).
        foreach (['user_id', 'plan_id', 'starts_at', 'ends_at', 'is_active', 'approved_by', 'approved_at'] as $field) {
            $this->request->remove($field);
        }
    }
}
