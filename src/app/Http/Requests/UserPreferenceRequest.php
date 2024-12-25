<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserPreferenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'preferred_sources' => 'array',
            'preferred_sources.*' => 'string',
            'preferred_categories' => 'array',
            'preferred_categories.*' => 'string',
            'preferred_authors' => 'array',
            'preferred_authors.*' => 'string',
        ];
    }

    public function messages(): array
    {
        return [
            'preferred_sources.required' => 'The preferred sources field is required.',
            'preferred_sources.array' => 'The preferred sources must be an array.',
            'preferred_sources.*.string' => 'Each source must be a valid string.',
            'preferred_categories.required' => 'The preferred categories field is required.',
            'preferred_categories.array' => 'The preferred categories must be an array.',
            'preferred_categories.*.string' => 'Each category must be a valid string.',
            'preferred_authors.required' => 'The preferred authors field is required.',
            'preferred_authors.array' => 'The preferred authors must be an array.',
            'preferred_authors.*.string' => 'Each author must be a valid string.',
        ];
    }
}
