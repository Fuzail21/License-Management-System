<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserLicenseRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'license_id' => 'required|exists:licenses,id',
            'assigned_date' => 'required|date',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date|after:start_date',
            'renewal_cycle' => 'required|in:monthly,yearly',
            'renewal_cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,expired,suspended',
        ];
    }
}
