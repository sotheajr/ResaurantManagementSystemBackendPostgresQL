<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmSavePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'records' => 'required|array|min:1',
            'records.*.user_id' => 'required|integer',
            'records.*.month_year' => 'sometimes|string',
            'month_year' => 'sometimes|string',
        ];
    }
}