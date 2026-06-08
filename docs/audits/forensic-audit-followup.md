# Delni Forensic Audit Follow-up

Date: 2026-06-05

This note records fixes and intentional decisions from the forensic audit follow-up. It reflects implementation-verified behavior and does not redefine confirmed business rules.

## Review moderation

Reviews are intentionally approved/live by default. Public review creation writes `status = approved` in `App\Http\Controllers\Public\ReviewController::store()`.

Moderation is after-the-fact:

- Users and providers can flag eligible reviews through `reviews.flag`.
- Admins inspect, reject, soft-delete, restore, keep, or mark flags handled through `App\Filament\Resources\ReviewResource`.
- Rating statistics are recalculated from approved, non-deleted reviews.

This is not treated as a vulnerability. The safety mechanism is flagging plus admin moderation.

## Review flag schema

The review flag handling fields are required by the implementation:

- `reviews.flag_handled_at`
- `reviews.flag_handled_by`

Migration:

- `database/migrations/2026_06_05_203316_add_flag_handled_columns_to_reviews_table.php`

Model alignment:

- `App\Models\Review` includes both fields in `$fillable`.
- `App\Models\Review` casts `flag_handled_at` to `datetime`.
- `App\Models\Review::flagHandledBy()` maps `flag_handled_by` to `App\Models\User`.

Regression coverage:

- `Tests\Feature\ReviewSystemHardeningTest::test_review_flag_handled_columns_exist_and_are_mass_assignable`

## Provider credentials

Decision: provider-managed.

Credentials are part of a provider's public trust profile, alongside links and portfolio items. Providers can now manage their own credentials in the provider panel. Public display remains enabled only for credentials attached to a discoverable provider profile.

Implementation:

- `App\Filament\Provider\Resources\ProviderCredentialResource`
- `App\Policies\ProviderCredentialPolicy`
- Registered in `App\Providers\AppServiceProvider`

Scope:

- Providers can list/create/edit/delete credentials only for their own profile.
- Other providers' credentials are not visible in the provider panel query.
- Super admins retain policy bypass if this model is exposed to admin workflows later.

Regression coverage:

- `Tests\Feature\BackendBusinessRulesTest::test_provider_credentials_are_provider_managed_and_publicly_visible`

## Provider subscriptions

Decision: admin-managed only.

`App\Filament\Provider\Resources\ProviderSubscriptionResource` remains intentionally disabled:

- `$shouldRegisterNavigation = false`
- `canAccess()` returns `false`

Providers should not create or manage subscriptions. They see read-only subscription status through `App\Filament\Provider\Widgets\SubscriptionStatusWidget`.

The widget intentionally shows only:

- active/inactive/no-subscription state
- plan label
- `ends_at`
- days remaining

It must not show payment references, payment method, notes, or internal payment data.

Regression coverage:

- `Tests\Feature\BackendBusinessRulesTest::test_provider_subscription_resource_not_accessible`
- `Tests\Feature\BackendBusinessRulesTest::test_provider_subscription_widget_is_read_only_and_does_not_show_payment_data`

## Marketplace placements

Decision: hidden operational admin feature.

Marketplace placements remain hidden from public users and providers. They are ranking controls stored on `profile_stats`, not public profile content.

Implementation:

- `App\Filament\Resources\MarketplacePlacementResource`
- `$shouldRegisterNavigation = false`
- Admin panel route remains protected by admin panel middleware.
- Public/provider pages must not expose raw placement field names or dates.

Regression coverage:

- `Tests\Feature\BackendBusinessRulesTest::test_marketplace_placement_routes_are_admin_only_and_public_output_hides_raw_flags`

## Password change redirect

Password changes now redirect by role:

- provider: provider panel
- super admin: admin panel
- normal user: dashboard

Implementation:

- `App\Http\Controllers\Auth\PasswordController::change`

Regression coverage:

- `Tests\Feature\AccountStatusSystemTest::test_password_change_redirects_provider_to_provider_panel`
- `Tests\Feature\AccountStatusSystemTest::test_password_change_redirects_super_admin_to_admin_panel`
- `Tests\Feature\AccountStatusSystemTest::test_password_change_redirects_public_user_to_dashboard`

## Unused FormRequest cleanup backlog

These request classes appear unused by current routes/Filament workflows and should be treated as cleanup backlog, not deleted during this fix:

- `App\Http\Requests\Auth\ForgotPasswordRequest`
- `App\Http\Requests\Auth\ResetPasswordRequest`
- `App\Http\Requests\Profile\FeatureProfileRequest`
- `App\Http\Requests\Profile\UpdateProfileRequest`
- `App\Http\Requests\Review\ModerateReviewRequest`
- `App\Http\Requests\Subscription\ApproveSubscriptionRequest`
- `App\Http\Requests\Subscription\CreateSubscriptionRequest`
- `App\Http\Requests\Subscription\UpdateSubscriptionRequest`
- `App\Http\Requests\User\CreateAdminRequest`
- `App\Http\Requests\User\CreateProviderRequest`

Reason for keeping them: several document intended invariants and may belong to older controller workflows or future non-Filament endpoints. Removing them safely requires a separate route/controller/test audit.
