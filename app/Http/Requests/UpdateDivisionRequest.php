<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Models\Division;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDivisionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $division = $this->route('division');

        if (!$this->user()->can('update', $division)) {
            return false;
        }

        // If manager is changing the department, verify they have access to the new department's city
        if ($this->user()->isManager() && $this->has('department_id')) {
            $newDepartmentId = $this->department_id;
            $currentDepartmentId = $division->department_id;

            // If department is changing, verify manager has access to new department
            if ($newDepartmentId != $currentDepartmentId) {
                return $this->user()->can('updateToDepartment', [Division::class, $division, $newDepartmentId]);
            }
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
        $division = $this->route('division');

        return [
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id'),
                function ($attribute, $value, $fail) use ($division) {
                    $department = Department::with('city')->find($value);

                    if (!$department || !$department->city) {
                        $fail('The selected department is invalid or incomplete.');
                        return;
                    }

                    // Managers can only update to departments they manage
                    if ($this->user()->isManager()) {
                        $managedCityIds = $this->user()->managedCities()->pluck('cities.id')->toArray();

                        // Verify manager has access to both current AND new department's city
                        $currentDepartment = $division->department;
                        if ($currentDepartment && !in_array($currentDepartment->city_id, $managedCityIds)) {
                            $fail('You do not have permission to update this division.');
                            return;
                        }

                        if (!in_array($department->city_id, $managedCityIds)) {
                            $fail('You do not have permission to move divisions to this department.');
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
    }
}
