<?php

declare(strict_types=1);

namespace App\Http\Requests\City;

use App\Models\City;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('city'));
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        $city = $this->route('city');

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                Rule::in($city instanceof City ? [$city->slug] : []),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'slug.in' => 'City slugs are immutable after creation and cannot be changed.',
        ];
    }
}
