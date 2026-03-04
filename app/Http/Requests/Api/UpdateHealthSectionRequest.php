<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHealthSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'main_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'overlapping_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'main_image.file' => 'Please select a valid image file.',
            'main_image.mimes' => 'Main image must be JPG, JPEG or PNG.',
            'main_image.max' => 'Main image must not be larger than 5 MB.',
            'overlapping_image.file' => 'Please select a valid image file.',
            'overlapping_image.mimes' => 'Overlapping image must be JPG, JPEG or PNG.',
            'overlapping_image.max' => 'Overlapping image must not be larger than 5 MB.',
        ];
    }
}
