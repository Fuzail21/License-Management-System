<?php

namespace App\Http\Requests;

use App\Models\City;
use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check basic create permission
        if (!$this->user()->can('create', Department::class)) {
            return false;
        }

        // For managers, verify they manage the specified city
        if ($this->user()->isManager() && $this->has('city_id')) {
            return $this->user()->can('createInCity', [Department::class, $this->city_id]);
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'city_id' => [
                'required',
                'integer',
                Rule::exists('cities', 'id'),
                function ($attribute, $value, $fail) {
                    // Validate city exists and is accessible
                    $city = City::find($value);

                    if (!$city) {
                        $fail('The selected city does not exist.');
                        return;
                    }

                    // Managers can only create in cities they manage
                    if ($this->user()->isManager()) {
                        $managedCityIds = $this->user()->managedCities()->pluck('cities.id')->toArray();

                        if (!in_array($value, $managedCityIds)) {
                            $fail('You do not have permission to create departments in this city.');
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
            'city_id.required' => 'City is required.',
            'city_id.exists' => 'The selected city does not exist.',
            'name.required' => 'Department name is required.',
            'name.min' => 'Department name must be at least 2 characters.',
            'name.max' => 'Department name cannot exceed 255 characters.',
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

        // Clean city_id (prevent string injection)
        if ($this->has('city_id')) {
            $this->merge([
                'city_id' => filter_var($this->city_id, FILTER_VALIDATE_INT),
            ]);
        }
    }
}
