<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in' => 'sometimes|nullable|date_format:H:i,H:i:s',
            'clock_out' => 'sometimes|nullable|date_format:H:i,H:i:s',
            'status' => 'sometimes|nullable|string|in:Present,Late,Half Day,Absent',
            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }
}