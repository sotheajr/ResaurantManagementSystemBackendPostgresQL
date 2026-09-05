<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'sometimes|required|exists:customers,id',
            'table_id' => 'sometimes|required|exists:tables,id',
            'reservation_date' => 'sometimes|date',
            'guest_number' => 'sometimes|required|integer|min:1',
            'status' => 'sometimes|required|string|in:Pending,Confirmed,Seated,Cancelled,No Show',
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