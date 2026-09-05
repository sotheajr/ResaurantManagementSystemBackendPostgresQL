<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\GeneratePayrollRequest;
use App\Http\Requests\Payroll\ConfirmSavePayrollRequest;
use App\Http\Requests\Payroll\UpdatePayrollRequest;
use App\Http\Requests\Payroll\PayPayrollRequest;
use App\Services\PayrollService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    use ApiResponseTrait;

    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * List payroll entries (optionally filtered by month_year / user_id).
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['month_year', 'user_id']);
            return $this->successResponse($this->payrollService->getAllPayrolls($filters));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            return $this->successResponse($this->payrollService->getPayrollById((int) $id));
        } catch (\Exception $e) {
            return $this->errorResponse('Payroll record not found', 404);
        }
    }

    /**
     * Calculate a payroll DRAFT for every active employee (nothing is saved).
     */
    public function preview(GeneratePayrollRequest $request)
    {
        try {
            $validated = $request->validated();
            $draft = $this->payrollService->calculateDraft(
                $validated['payroll_type'] ?? 'Monthly',
                $validated['month_year'],
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );
            return $this->successResponse($draft);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Persist a confirmed draft.
     */
    public function confirmSave(ConfirmSavePayrollRequest $request)
    {
        try {
            // NOTE: use $request->input('records') instead of validated() —
            // Laravel's validated() only returns keys that have validation
            // rules, which would strip base_salary/ot_amount/etc. from the
            // records. The records come from our own server-generated preview.
            $saved = $this->payrollService->savePayrollRecords(
                $request->input('records', []),
                $request->input('month_year')
            );
            return $this->successResponse($saved, 'Payroll saved successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * One-shot generate + save payroll for a month (from attendance records).
     */
    public function generate(GeneratePayrollRequest $request)
    {
        try {
            $saved = $this->payrollService->generateMonthlyPayroll($request->validated()['month_year']);
            return $this->successResponse($saved, 'Payroll generated successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Manually adjust bonus / deductions (legacy uppercase contract supported).
     */
    public function update(UpdatePayrollRequest $request, $id)
    {
        try {
            $payroll = $this->payrollService->updatePayroll((int) $id, $this->mapAdjustments($request->validated()));
            return $this->successResponse($payroll, 'Payroll updated successfully');
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            return $this->errorResponse($e->getMessage(), $code >= 400 && $code < 600 ? $code : 500);
        }
    }

    /**
     * Mark a payroll as Paid and set the payment date.
     */
    public function pay(PayPayrollRequest $request, $id)
    {
        try {
            $paymentDate = $request->validated()['payment_date'] ?? null;
            $payroll = $this->payrollService->markAsPaid((int) $id, $paymentDate);
            return $this->successResponse($payroll, 'Payroll marked as Paid');
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            return $this->errorResponse($e->getMessage(), $code >= 400 && $code < 600 ? $code : 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->payrollService->deletePayroll((int) $id);
            return $this->successResponse(null, 'Payroll record deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Payroll record not found', 404);
        }
    }

    /**
     * Map the legacy uppercase adjustment fields onto the payroll columns:
     * bonus      = Bonuses_Tips + Manual_Bonus
     * deductions = Deductions + Late_Penalties + Manual_Deduction
     */
    private function mapAdjustments(array $data): array
    {
        $update = [];

        if (array_key_exists('Base_Salary', $data)) {
            $update['base_salary'] = (float) $data['Base_Salary'];
        } elseif (array_key_exists('base_salary', $data)) {
            $update['base_salary'] = (float) $data['base_salary'];
        }

        if (array_key_exists('Overtime_Pay', $data)) {
            $update['ot_amount'] = (float) $data['Overtime_Pay'];
        }

        if (array_key_exists('Total_OT_Hours', $data)) {
            $update['total_ot_hours'] = (float) $data['Total_OT_Hours'];
        }

        $bonusParts = [];
        foreach (['Bonuses_Tips', 'Manual_Bonus', 'bonus'] as $key) {
            if (array_key_exists($key, $data)) {
                $bonusParts[] = (float) $data[$key];
            }
        }
        if ($bonusParts) {
            $update['bonus'] = round(array_sum($bonusParts), 2);
        }

        $deductionParts = [];
        foreach (['Deductions', 'Late_Penalties', 'Manual_Deduction', 'deductions'] as $key) {
            if (array_key_exists($key, $data)) {
                $deductionParts[] = (float) $data[$key];
            }
        }
        if ($deductionParts) {
            $update['deductions'] = round(array_sum($deductionParts), 2);
        }

        return $update;
    }
}