<?php

namespace App\Http\Requests;

use App\Models\City;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', City::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'code' => [
                'nullable',
                'string',
                'max:10',
                'uppercase',
                'alpha_dash',
                Rule::unique('cities', 'code'),
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'City name is required.',
            'name.min' => 'City name must be at least 2 characters.',
            'name.max' => 'City name cannot exceed 255 characters.',
            'code.unique' => 'This city code is already in use.',
            'code.uppercase' => 'City code must be uppercase.',
            'code.alpha_dash' => 'City code may only contain letters, numbers, dashes, and underscores.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either active or inactive.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Automatically uppercase the code
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper($this->code),
            ]);
        }

        // Set default status if not provided
        if (!$this->has('status')) {
            $this->merge([
                'status' => 'active',
            ]);
        }
    }
}
