<?php

declare(strict_types=1);

namespace App\Http\Requests\Search;

use App\Models\Subcategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Public profile search/browse endpoint.
 *
 * Subcategory/category consistency is enforced here the same way as in
 * UpdateProfileRequest: the submitted subcategory_id must belong to the
 * submitted category_id. Without this check, a user could submit
 * category_id=1 + subcategory_id=99 (belonging to category 2) and
 * receive unexpected results or bypass category-level filtering.
 */
class SearchProfilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'city_id' => [
                'nullable',
                Rule::exists('cities', 'id')->where('is_active', true)->whereNull('deleted_at'),
            ],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('is_active', true)->whereNull('deleted_at'),
            ],
            'subcategory_id' => ['nullable', 'integer', $this->subcategoryBelongsToCategory()],
            'provider_type' => [
                'nullable',
                Rule::exists('provider_types', 'code')->where('is_active', true),
            ],
            'remote' => ['nullable', 'boolean'],
            'keyword' => ['nullable', 'string', 'min:2', 'max:100'],
            'sort' => ['nullable', Rule::in(['rating', 'reviews', 'featured', 'newest'])],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    private function subcategoryBelongsToCategory(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! $this->filled('category_id')) {
                // Searching by subcategory without a category is ambiguous —
                // still valid (backend should JOIN to find the parent category),
                // but warn if the subcategory doesn't exist at all.
                $exists = Subcategory::where('id', $value)->where('is_active', true)->exists();

                if (! $exists) {
                    $fail('The selected subcategory is not available.');
                }

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
        if ($this->has('keyword')) {
            $this->merge([
                // strip_tags prevents XSS if keyword is ever rendered unescaped.
                // Blade's {{ }} escapes anyway, but defense-in-depth at entry point.
                'keyword' => strip_tags(trim((string) $this->input('keyword'))),
            ]);
        }
    }
}
