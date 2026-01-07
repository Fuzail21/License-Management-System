<?php

namespace App\Http\Requests;

use App\Models\Division;
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

        // If manager is changing the division, verify they have access to the new division's city
        if ($this->user()->isManager() && $this->has('division_id')) {
            $newDivisionId = $this->division_id;
            $currentDivisionId = $employee->division_id;

            // If division is changing, verify manager has access to new division
            if ($newDivisionId != $currentDivisionId) {
                return $this->user()->can('updateToDivision', [Employee::class, $employee, $newDivisionId]);
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
            'division_id' => [
                'required',
                'integer',
                Rule::exists('divisions', 'id'),
                function ($attribute, $value, $fail) use ($employee) {
                    // Validate division exists and is accessible through city hierarchy
                    $division = Division::with('department.city')->find($value);

                    if (!$division || !$division->department || !$division->department->city) {
                        $fail('The selected division is invalid or incomplete.');
                        return;
                    }

                    // Managers can only update to divisions they manage
                    if ($this->user()->isManager()) {
                        $managedCityIds = $this->user()->managedCities()->pluck('cities.id')->toArray();

                        // Verify manager has access to both current AND new division's city
                        $currentDivision = $employee->division;
                        if ($currentDivision && $currentDivision->department) {
                            $currentCityId = $currentDivision->department->city_id;
                            if (!in_array($currentCityId, $managedCityIds)) {
                                $fail('You do not have permission to update this employee.');
                                return;
                            }
                        }

                        $newCityId = $division->department->city_id;
                        if (!in_array($newCityId, $managedCityIds)) {
                            $fail('You do not have permission to move employees to this division.');
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
            'division_id.required' => 'Division is required.',
            'division_id.exists' => 'The selected division does not exist.',
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
        // Sanitize division_id
        if ($this->has('division_id')) {
            $this->merge([
                'division_id' => filter_var($this->division_id, FILTER_VALIDATE_INT),
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
