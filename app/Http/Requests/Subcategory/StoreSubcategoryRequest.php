<?php

declare(strict_types=1);

namespace App\Http\Requests\Subcategory;

use App\Models\Subcategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubcategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Subcategory::class);
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('is_active', true)->whereNull('deleted_at'),
            ],
            'slug' => ['required', 'string', 'max:255', 'unique:subcategories,slug', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'The selected category is not available.',
            'slug.regex' => 'Slug may only contain lowercase letters, numbers, and hyphens.',
        ];
    }
}
