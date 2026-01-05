<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'department_id' => 'nullable|exists:departments,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $this->route('employee')->id,
            'phone' => 'nullable|string|max:20',
            'designation' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ];
    }
}
