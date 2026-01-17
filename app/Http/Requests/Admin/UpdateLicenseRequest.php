<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLicenseRequest extends FormRequest
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
            'vendor_id' => 'required|exists:vendors,id',
            'license_name' => 'required|string|max:255',
            'renewal_type' => 'required|in:subscription,perpetual',
            'renewal_cycle' => 'nullable|in:monthly,quarterly,yearly,perpetual',
            'number_license_assigned' => 'nullable|integer|min:0',
            'version' => 'nullable|string|max:50',
            'max_users' => 'nullable|integer|min:1',
            'cost' => 'required|numeric|min:0',
            'renewal_date' => 'nullable|date',
            'description' => 'nullable|string',
        ];
    }
}
