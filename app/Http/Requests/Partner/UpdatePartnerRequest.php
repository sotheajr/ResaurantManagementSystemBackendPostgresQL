<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Accept legacy frontend field names (company_name / contact_person)
     * BEFORE validation runs. Conditional merge keeps partial updates working
     * with the `sometimes` rule (no null keys injected).
     */
    protected function prepareForValidation(): void
    {
        if ($this->missing('partner_name') && $this->has('company_name')) {
            $this->merge(['partner_name' => $this->input('company_name')]);
        }
        if ($this->missing('contact_name') && $this->has('contact_person')) {
            $this->merge(['contact_name' => $this->input('contact_person')]);
        }
    }

    public function rules(): array
    {
        return [
            'partner_name' => 'sometimes|required|string|max:150',
            'contact_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:255',
            'partnership_type' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }
}