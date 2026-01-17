<?php

namespace App\Http\Requests;

use App\Models\Division;
use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $department = $this->route('department');

        if (!$this->user()->can('update', $department)) {
            return false;
        }

        // If manager is changing the division, verify they have access to the new division's city
        if ($this->user()->isManager() && $this->has('division_id')) {
            $newDivisionId = $this->division_id;
            $currentDivisionId = $department->division_id;

            // If division is changing, verify manager has access to new division
            if ($newDivisionId != $currentDivisionId) {
                return $this->user()->can('updateToDivision', [Department::class, $department, $newDivisionId]);
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
        $department = $this->route('department');

        return [
            'division_id' => [
                'required',
                'integer',
                Rule::exists('divisions', 'id'),
                function ($attribute, $value, $fail) use ($department) {
                    $division = Division::with('city')->find($value);

                    if (!$division || !$division->city) {
                        $fail('The selected division is invalid or incomplete.');
                        return;
                    }

                    // Managers can only update to divisions they manage
                    if ($this->user()->isManager()) {
                        $managedCityIds = $this->user()->managedCities()->pluck('cities.id')->toArray();

                        // Verify manager has access to both current AND new division's city
                        $currentDivision = $department->division;
                        if ($currentDivision && !in_array($currentDivision->city_id, $managedCityIds)) {
                            $fail('You do not have permission to update this department.');
                            return;
                        }

                        if (!in_array($division->city_id, $managedCityIds)) {
                            $fail('You do not have permission to move departments to this division.');
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
            'division_id.required' => 'Division is required.',
            'division_id.exists' => 'The selected division does not exist.',
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
        // Sanitize division_id
        if ($this->has('division_id')) {
            $this->merge([
                'division_id' => filter_var($this->division_id, FILTER_VALIDATE_INT),
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
