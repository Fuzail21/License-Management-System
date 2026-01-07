<?php

namespace App\Http\Requests;

use App\Models\City;
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

        // If manager is changing the city, verify they have access to the new city
        if ($this->user()->isManager() && $this->has('city_id')) {
            $newCityId = $this->city_id;
            $currentCityId = $department->city_id;

            // If city is changing, verify manager has access to new city
            if ($newCityId != $currentCityId) {
                return $this->user()->can('updateToCity', [Department::class, $department, $newCityId]);
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
            'city_id' => [
                'required',
                'integer',
                Rule::exists('cities', 'id'),
                function ($attribute, $value, $fail) use ($department) {
                    $city = City::find($value);

                    if (!$city) {
                        $fail('The selected city does not exist.');
                        return;
                    }

                    // Managers can only update to cities they manage
                    if ($this->user()->isManager()) {
                        $managedCityIds = $this->user()->managedCities()->pluck('cities.id')->toArray();

                        // Verify manager has access to both current AND new city
                        if (!in_array($department->city_id, $managedCityIds)) {
                            $fail('You do not have permission to update this department.');
                            return;
                        }

                        if (!in_array($value, $managedCityIds)) {
                            $fail('You do not have permission to move departments to this city.');
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
        // Sanitize city_id
        if ($this->has('city_id')) {
            $this->merge([
                'city_id' => filter_var($this->city_id, FILTER_VALIDATE_INT),
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
