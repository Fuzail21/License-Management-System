<?php

namespace App\Http\Requests;

use App\Models\Department;
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
        if (!$this->user()->can('create', Division::class)) {
            return false;
        }

        // Verify manager has access to the department's city
        if ($this->user()->isManager() && $this->has('department_id')) {
            return $this->user()->can('createInDepartment', [Division::class, $this->department_id]);
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
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id'),
                function ($attribute, $value, $fail) {
                    // Validate department exists and is accessible through city hierarchy
                    $department = Department::with('city')->find($value);

                    if (!$department || !$department->city) {
                        $fail('The selected department is invalid or incomplete.');
                        return;
                    }

                    // Managers can only create divisions in departments they manage
                    if ($this->user()->isManager()) {
                        $cityId = $department->city_id;
                        $managedCityIds = $this->user()->managedCities()->pluck('cities.id')->toArray();

                        if (!in_array($cityId, $managedCityIds)) {
                            $fail('You do not have permission to create divisions in this department.');
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
            'department_id.required' => 'Department is required.',
            'department_id.exists' => 'The selected department does not exist.',
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
        // Sanitize department_id
        if ($this->has('department_id')) {
            $this->merge([
                'department_id' => filter_var($this->department_id, FILTER_VALIDATE_INT),
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
