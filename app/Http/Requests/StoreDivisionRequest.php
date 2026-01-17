<?php

namespace App\Http\Requests;

use App\Models\City;
use App\Models\Division;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDivisionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check basic create permission
        if (!$this->user()->can('create', Division::class)) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'city_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'city_ids.*' => [
                'required',
                'integer',
                Rule::exists('cities', 'id'),
                function ($attribute, $value, $fail) {
                    // Validate city exists and is accessible
                    $city = City::find($value);

                    if (!$city) {
                        $fail('One of the selected cities does not exist.');
                        return;
                    }

                    // Managers can only create in cities they manage
                    if ($this->user()->isManager()) {
                        $managedCityIds = $this->user()->managedCities()->pluck('cities.id')->toArray();

                        if (!in_array($value, $managedCityIds)) {
                            $fail('You do not have permission to create divisions in one or more selected cities.');
                        }
                    }
                },
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
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
            'city_ids.required' => 'At least one city is required.',
            'city_ids.array' => 'Cities must be an array.',
            'city_ids.min' => 'At least one city must be selected.',
            'city_ids.*.exists' => 'One of the selected cities does not exist.',
            'name.required' => 'Division name is required.',
            'name.min' => 'Division name must be at least 2 characters.',
            'name.max' => 'Division name cannot exceed 255 characters.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either active or inactive.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default status if not provided
        if (!$this->has('status')) {
            $this->merge([
                'status' => 'active',
            ]);
        }

        // Clean city_ids (prevent string injection)
        if ($this->has('city_ids') && is_array($this->city_ids)) {
            $this->merge([
                'city_ids' => array_map(function ($id) {
                    return filter_var($id, FILTER_VALIDATE_INT);
                }, $this->city_ids),
            ]);
        }
    }
}
