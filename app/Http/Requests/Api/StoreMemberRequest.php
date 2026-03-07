<?php

namespace App\Http\Requests\Api;

use App\PrimaryMemberType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    /**
     * Normalize phone to 11 digits (BD format) before validation.
     */
    public function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $phone = $this->phone;
            $phone = preg_replace('/[^0-9]/', '', (string) $phone);
            if (strlen($phone) === 13 && str_starts_with($phone, '880')) {
                $phone = substr($phone, 2);
            }
            $this->merge(['phone' => $phone]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'size:11', Rule::unique('users', 'phone')],
            'primary_member_type' => ['required', 'string', Rule::enum(PrimaryMemberType::class)],
            'ssc_year' => ['nullable', 'integer', 'min:1950', 'max:2100'],
            'jsc_year' => ['nullable', 'integer', 'min:1950', 'max:2100'],
        ];
    }

    /**
     * Configure the validator to require at least one of ssc_year or jsc_year.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('ssc_year') || $this->filled('jsc_year')) {
                return;
            }
            $validator->errors()->add(
                'ssc_year',
                'Either SSC year or JSC year is required to generate Member ID.'
            );
        });
    }
}
