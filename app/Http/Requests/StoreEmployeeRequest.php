<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check basic create permission
        if (!$this->user()->can('create', Employee::class)) {
            return false;
        }

        // For managers, verify they manage the department's division's city
        if ($this->user()->isManager() && $this->has('department_id')) {
            return $this->user()->can('createInDepartment', [Employee::class, $this->department_id]);
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
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
                    $department = Department::with('division.city')->find($value);

                    if (!$department || !$department->division || !$department->division->city) {
                        $fail('The selected department is invalid or incomplete.');
                        return;
                    }

                    // Managers can only create employees in departments they manage
                    if ($this->user()->isManager()) {
                        $cityId = $department->division->city_id;
                        $managedCityIds = $this->user()->managedCities()->pluck('cities.id')->toArray();

                        if (!in_array($cityId, $managedCityIds)) {
                            $fail('You do not have permission to create employees in this department.');
                        }
                    }
                },
            ],
            'employee_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'employee_number'),
            ],
            'first_name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'last_name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email'),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'hire_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['active', 'inactive', 'terminated', 'on_leave']),
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
            'employee_number.required' => 'Employee number is required.',
            'employee_number.unique' => 'This employee number is already in use.',
            'first_name.required' => 'First name is required.',
            'first_name.min' => 'First name must be at least 2 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already in use.',
            'hire_date.before_or_equal' => 'Hire date cannot be in the future.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be active, inactive, terminated, or on_leave.',
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

        // Clean department_id (prevent string injection)
        if ($this->has('department_id')) {
            $this->merge([
                'department_id' => filter_var($this->department_id, FILTER_VALIDATE_INT),
            ]);
        }

        // Sanitize names
        if ($this->has('first_name')) {
            $this->merge([
                'first_name' => trim($this->first_name),
            ]);
        }

        if ($this->has('last_name')) {
            $this->merge([
                'last_name' => trim($this->last_name),
            ]);
        }

        // Normalize email
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->email)),
            ]);
        }
    }
}
