<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,preparing,ready,completed,cancelled',
            'payment_status' => 'nullable|not_in:paid,Paid,PAID',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status. Allowed values: pending, preparing, ready, completed, cancelled.',
            'payment_status.not_in' => 'Payment completion must happen through the checkout/payment flow. Manual paid updates are not allowed.',
        ];
    }
}
