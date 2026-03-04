<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthPageSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.file' => 'Please select a valid image file.',
            'image.mimes' => 'Image must be JPG, JPEG or PNG.',
            'image.max' => 'Image must not be larger than 5 MB.',
        ];
    }
}
