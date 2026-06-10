<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadIconRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:icons'],
            'file' => ['required', 'file', 'mimes:svg', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'Only SVG files allowed.',
            'file.max' => 'File must be less than 500KB.',
        ];
    }
}
