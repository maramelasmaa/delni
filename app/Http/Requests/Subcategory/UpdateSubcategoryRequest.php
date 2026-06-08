<?php

declare(strict_types=1);

namespace App\Http\Requests\Subcategory;

use App\Models\Subcategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Slug immutability: profile_subcategory pivot rows reference subcategory IDs,
 * not slugs, so slug changes don't corrupt pivot data — but they do break
 * any URL-based subcategory filters. Enforce immutability at request level.
 *
 * category_id MAY be changed (re-categorize a subcategory). The pivot table
 * has a composite unique constraint on (profile_id, subcategory_id) but not on
 * (subcategory_id, category_id). Changing category_id here would silently make
 * existing profile-subcategory associations point to a subcategory in a new
 * category — callers should be aware this may require re-validating profiles.
 */
class UpdateSubcategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('subcategory'));
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        $subcategory = $this->route('subcategory');

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('is_active', true)->whereNull('deleted_at'),
            ],
            'slug' => [
                'required',
                'string',
                Rule::in($subcategory instanceof Subcategory ? [$subcategory->slug] : []),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'slug.in' => 'Subcategory slugs are immutable after creation and cannot be changed.',
            'category_id.exists' => 'The selected category is not available.',
        ];
    }
}
