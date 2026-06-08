<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\Models\Subcategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Provider updating their own business profile.
 *
 * CRITICAL: This request enforces category/subcategory consistency —
 * the invariant that was previously unenforced because ProfileObserver::saving()
 * referenced $profile->subcategory_id which does not exist as a column
 * (subcategories are a BelongsToMany pivot relationship).
 *
 * The saving() observer lifecycle also fires before pivot sync, making it
 * architecturally impossible to validate BelongsToMany data there.
 * The Form Request is the correct and only layer for this invariant.
 *
 * Rule: every ID in subcategory_ids must belong to the submitted category_id.
 * If category_id is null, no subcategory_ids may be submitted.
 * If category_id changes, all existing subcategory_ids are implicitly re-validated
 * against the new category on the next save.
 */
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('profile'));
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'business_name' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(['individual', 'business'])],
            'provider_type' => [
                'required',
                Rule::exists('provider_types', 'code')->where('is_active', true),
            ],
            'bio' => ['nullable', 'string', 'max:2000'],
            'city_id' => [
                'nullable',
                Rule::exists('cities', 'id')->where('is_active', true)->whereNull('deleted_at'),
            ],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('is_active', true)->whereNull('deleted_at'),
            ],
            'subcategory_ids' => ['nullable', 'array'],
            'subcategory_ids.*' => ['integer', $this->subcategoryBelongsToCategory()],
            'whatsapp' => ['required', 'string', 'max:15', 'regex:/^[1-9][0-9]{7,14}$/'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[+0-9][0-9\s\-()]{6,19}$/'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'offers_remote_work' => ['nullable', 'boolean'],
            'map_url' => ['nullable', 'url', 'max:255'],
            'service_area_note' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    /**
     * Validates that each subcategory ID belongs to the submitted category.
     * This is the sole enforcement point for the category/subcategory consistency
     * invariant that was previously unenforced at all layers.
     */
    private function subcategoryBelongsToCategory(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! $this->filled('category_id')) {
                $fail('Subcategories require a category to be selected first.');

                return;
            }

            $valid = Subcategory::query()
                ->where('id', $value)
                ->where('category_id', $this->integer('category_id'))
                ->where('is_active', true)
                ->exists();

            if (! $valid) {
                $fail('The selected subcategory does not belong to the selected category.');
            }
        };
    }

    protected function prepareForValidation(): void
    {
        // slug is set on creation by the system and must never change (URLs).
        // is_complete is computed by ProfileCompletenessService, not user-supplied.
        // user_id must never change.
        $this->request->remove('slug');
        $this->request->remove('is_complete');
        $this->request->remove('user_id');
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'city_id.exists' => 'The selected city is not available.',
            'category_id.exists' => 'The selected category is not available.',
            'logo.max' => 'Logo may not exceed 2 MB.',
            'cover_image.max' => 'Cover image may not exceed 4 MB.',
            'logo.mimes' => 'Logo must be a JPG, PNG, or WebP image.',
            'cover_image.mimes' => 'Cover image must be a JPG, PNG, or WebP image.',
            'whatsapp.regex' => 'يرجى إدخال رقم واتساب بصيغة wa.me: رمز الدولة ثم الرقم بدون + أو مسافات أو صفر البداية.',
        ];
    }
}
