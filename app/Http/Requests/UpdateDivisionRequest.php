<?php

namespace App\Http\Requests;

use App\Models\City;
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

        // If manager is changing the city, verify they have access to the new city
        if ($this->user()->isManager() && $this->has('city_id')) {
            $newCityId = $this->city_id;
            $currentCityId = $division->city_id;

            // If city is changing, verify manager has access to new city
            if ($newCityId != $currentCityId) {
                return $this->user()->can('updateToCity', [Division::class, $division, $newCityId]);
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
            'city_id' => [
                'required',
                'integer',
                Rule::exists('cities', 'id'),
                function ($attribute, $value, $fail) use ($division) {
                    $city = City::find($value);

                    if (!$city) {
                        $fail('The selected city does not exist.');
                        return;
                    }

                    // Managers can only update to cities they manage
                    if ($this->user()->isManager()) {
                        $managedCityIds = $this->user()->managedCities()->pluck('cities.id')->toArray();

                        // Verify manager has access to both current AND new city
                        if (!in_array($division->city_id, $managedCityIds)) {
                            $fail('You do not have permission to update this division.');
                            return;
                        }

                        if (!in_array($value, $managedCityIds)) {
                            $fail('You do not have permission to move divisions to this city.');
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
