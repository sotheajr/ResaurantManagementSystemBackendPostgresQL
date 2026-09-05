<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class GeneratePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // NOTE: array syntax is required because the regex contains a pipe,
        // which Laravel's pipe-separated rule string parser would split on.
        return [
            'month_year' => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'payroll_type' => ['nullable', 'string', 'in:Monthly,Weekly,Custom'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'week_number' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];
    }
}