<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'table_id' => 'required|exists:tables,id',
            'reservation_date' => 'nullable|date',
            'guest_number' => 'required|integer|min:1',
            'status' => 'nullable|string|in:Pending,Confirmed,Seated,Cancelled,No Show',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.exists' => 'The selected customer does not exist.',
            'table_id.exists' => 'The selected table does not exist.',
        ];
    }
}