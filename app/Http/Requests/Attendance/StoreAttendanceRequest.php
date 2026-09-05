<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,user_id',
            'date' => 'required|date',
            'clock_in' => 'nullable|date_format:H:i,H:i:s',
            'clock_out' => 'nullable|date_format:H:i,H:i:s',
            'status' => 'nullable|string|in:Present,Late,Half Day,Absent',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}