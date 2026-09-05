<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Accept BOTH the legacy uppercase contract and lowercase names
        return [
            'Base_Salary' => 'sometimes|numeric|min:0',
            'base_salary' => 'sometimes|numeric|min:0',
            'Overtime_Pay' => 'sometimes|numeric|min:0',
            'Total_OT_Hours' => 'sometimes|numeric|min:0',
            'Bonuses_Tips' => 'sometimes|numeric|min:0',
            'Manual_Bonus' => 'sometimes|numeric|min:0',
            'Deductions' => 'sometimes|numeric|min:0',
            'Late_Penalties' => 'sometimes|numeric|min:0',
            'Manual_Deduction' => 'sometimes|numeric|min:0',
            'bonus' => 'sometimes|numeric|min:0',
            'deductions' => 'sometimes|numeric|min:0',
        ];
    }
}