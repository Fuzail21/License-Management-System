<?php

namespace App\Http\Requests;

use App\Models\Division;
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
        if (!$this->user()->can('create', Department::class)) {
            return false;
        }

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
            'division_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'division_ids.*' => [
                'required',
                'integer',
                Rule::exists('divisions', 'id'),
                function ($attribute, $value, $fail) {
                    // Validate division exists and is accessible through city hierarchy
                    $division = Division::with('city')->find($value);

                    if (!$division || !$division->city) {
                        $fail('One of the selected divisions is invalid or incomplete.');
                        return;
                    }

                    // Managers can only create departments in divisions they manage
                    if ($this->user()->isManager()) {
                        $cityId = $division->city_id;
                        $managedCityIds = $this->user()->managedCities()->pluck('cities.id')->toArray();

                        if (!in_array($cityId, $managedCityIds)) {
                            $fail('You do not have permission to create departments in one or more selected divisions.');
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
            'division_ids.required' => 'At least one division is required.',
            'division_ids.array' => 'Divisions must be an array.',
            'division_ids.min' => 'At least one division must be selected.',
            'division_ids.*.exists' => 'One of the selected divisions does not exist.',
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
        // Sanitize division_ids
        if ($this->has('division_ids') && is_array($this->division_ids)) {
            $this->merge([
                'division_ids' => array_map(function ($id) {
                    return filter_var($id, FILTER_VALIDATE_INT);
                }, $this->division_ids),
            ]);
        }

        // Sanitize name
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
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
