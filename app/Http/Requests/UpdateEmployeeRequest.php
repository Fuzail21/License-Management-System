<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $employee = $this->route('employee');

        if (!$this->user()->can('update', $employee)) {
            return false;
        }

        // If manager is changing the department, verify they have access to the new department's city
        if ($this->user()->isManager() && $this->has('department_id')) {
            $newDepartmentId = $this->department_id;
            $currentDepartmentId = $employee->department_id;

            // If department is changing, verify manager has access to new department
            if ($newDepartmentId != $currentDepartmentId) {
                return $this->user()->can('updateToDepartment', [Employee::class, $employee, $newDepartmentId]);
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
        $employee = $this->route('employee');
        $employeeId = $employee->id;

        return [
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id'),
                function ($attribute, $value, $fail) use ($employee) {
                    // Validate department exists and is accessible through city hierarchy
                    $department = Department::with('division.city')->find($value);

                    if (!$department || !$department->division || !$department->division->city) {
                        $fail('The selected department is invalid or incomplete.');
                        return;
                    }

                    // Managers can only update to departments they manage
                    if ($this->user()->isManager()) {
                        $managedCityIds = $this->user()->managedCities()->pluck('cities.id')->toArray();

                        // Verify manager has access to both current AND new department's city
                        $currentDepartment = $employee->department;
                        if ($currentDepartment && $currentDepartment->division) {
                            $currentCityId = $currentDepartment->division->city_id;
                            if (!in_array($currentCityId, $managedCityIds)) {
                                $fail('You do not have permission to update this employee.');
                                return;
                            }
                        }

                        $newCityId = $department->division->city_id;
                        if (!in_array($newCityId, $managedCityIds)) {
                            $fail('You do not have permission to move employees to this department.');
                        }
                    }
                },
            ],
            'employee_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'employee_number')->ignore($employeeId),
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
                Rule::unique('employees', 'email')->ignore($employeeId),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\d\s\-\+\(\)]+$/',
            ],
            'hire_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'job_title' => [
                'required',
                'string',
                'max:255',
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['active', 'inactive', 'on_leave', 'terminated']),
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
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'phone.regex' => 'Please provide a valid phone number.',
            'hire_date.required' => 'Hire date is required.',
            'hire_date.date' => 'Please provide a valid date.',
            'hire_date.before_or_equal' => 'Hire date cannot be in the future.',
            'job_title.required' => 'Job title is required.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be one of: active, inactive, on_leave, or terminated.',
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

        // Sanitize phone
        if ($this->has('phone')) {
            $this->merge([
                'phone' => trim($this->phone),
            ]);
        }

        // Sanitize job title
        if ($this->has('job_title')) {
            $this->merge([
                'job_title' => trim($this->job_title),
            ]);
        }
    }
}
