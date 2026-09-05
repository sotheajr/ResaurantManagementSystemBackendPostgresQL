<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerId = $this->route('id');

        return [
            'customer_name' => 'sometimes|required|string|max:255',
            'phone' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('customers', 'phone')->ignore($customerId),
            ],
            'email' => [
                'nullable',
                'email',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'address' => 'nullable|string|max:500',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }
}
