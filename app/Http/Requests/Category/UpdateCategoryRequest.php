<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Slug immutability: changing a category slug would break every URL and
 * profile filter in the application. Enforced here by requiring the submitted
 * slug to match the existing slug exactly.
 */
class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('category'));
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                // Slug is immutable after creation. The only valid value is the current one.
                Rule::in($category instanceof Category ? [$category->slug] : []),
            ],
            'icon' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'slug.in' => 'Category slugs are immutable after creation and cannot be changed.',
        ];
    }
}
