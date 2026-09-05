<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use App\Repositories\Contracts\PayrollRepositoryInterface;

class PayrollService
{
    /** Flat deduction per late arrival. */
    private const LATE_FLAT_RATE = 2.00;

    /** Standard work hours per day (anything beyond this is overtime). */
    private const STANDARD_HOURS = 8;

    /** Overtime pay multiplier. */
    private const OT_MULTIPLIER = 1.5;

    /** Average working days per month, used to derive the hourly rate. */
    private const WORKING_DAYS = 26;

    protected $payrollRepository;

    public function __construct(PayrollRepositoryInterface $payrollRepository)
    {
        $this->payrollRepository = $payrollRepository;
    }

    public function getAllPayrolls(array $filters = [])
    {
        return $this->payrollRepository->getAll($filters)
            ->map(fn ($p) => $this->mapPayroll($p));
    }

    public function getPayrollById(int $id)
    {
        return $this->mapPayroll($this->payrollRepository->findById($id));
    }

    /**
     * Calculate a payroll DRAFT for every active employee (nothing is saved).
     */
    public function calculateDraft(string $payrollType, string $monthYear, ?string $startDate = null, ?string $endDate = null)
    {
        $users = User::with('role:role_id,role_name,role_name_kh')
            ->where('status', 'Active')
            ->orderBy('role_id')
            ->orderBy('user_id')
            ->get();

        $records = $users
            ->map(fn ($user) => $this->calculateForUser($user, $payrollType, $monthYear, $startDate, $endDate))
            ->values();

        return [
            'payroll_type' => $payrollType,
            'month_year' => $monthYear,
            'records' => $records,
        ];
    }

    /**
     * Persist a list of payroll records (from a confirmed draft).
     */
    public function savePayrollRecords(array $records, ?string $defaultMonthYear = null)
    {
        $saved = [];

        foreach ($records as $record) {
            $record = (array) $record;

            $userId = (int) ($record['user_id'] ?? $record['User_ID'] ?? 0);
            $monthYear = $record['month_year'] ?? $record['Month_Year'] ?? $defaultMonthYear;

            if (!$userId || !$monthYear) {
                continue;
            }

            $baseSalary = (float) ($record['base_salary'] ?? $record['Base_Salary'] ?? 0);
            $totalPresentDays = (int) ($record['total_present_days'] ?? $record['Total_Present_Days'] ?? 0);
            $totalAbsentDays = (int) ($record['total_absent_days'] ?? $record['Total_Absent_Days'] ?? 0);
            $totalOtHours = (float) ($record['total_ot_hours'] ?? $record['Total_OT_Hours'] ?? 0);
            $otAmount = (float) ($record['ot_amount'] ?? $record['Overtime_Pay'] ?? 0);
            $deductions = (float) ($record['deductions'] ?? $record['Deductions'] ?? 0);

            // Preserve manually set bonuses when regenerating a month
            $existing = $this->payrollRepository->findByUserAndMonth($userId, $monthYear);
            $bonus = $existing?->bonus ?? (float) ($record['bonus'] ?? $record['Bonuses_Tips'] ?? 0);

            $netSalary = round(max(0, $baseSalary + $otAmount + $bonus - $deductions), 2);

            $payroll = $this->payrollRepository->updateOrCreate(
                ['user_id' => $userId, 'month_year' => $monthYear],
                [
                    'base_salary' => $baseSalary,
                    'total_present_days' => $totalPresentDays,
                    'total_absent_days' => $totalAbsentDays,
                    'total_ot_hours' => $totalOtHours,
                    'ot_amount' => $otAmount,
                    'bonus' => $bonus,
                    'deductions' => $deductions,
                    'net_salary' => $netSalary,
                ]
            );

            $saved[] = $this->mapPayroll($payroll->fresh([
                'user:user_id,username,full_name,role_id,salary',
                'user.role:role_id,role_name,role_name_kh',
            ]));
        }

        return $saved;
    }

    /**
     * One-shot generate: calculate + save for a month.
     */
    public function generateMonthlyPayroll(string $monthYear)
    {
        $draft = $this->calculateDraft('Monthly', $monthYear);
        // Records is a Collection — convert to array for savePayrollRecords()
        return $this->savePayrollRecords($draft['records']->toArray(), $monthYear);
    }

    public function updatePayroll(int $id, array $data)
    {
        $payroll = $this->payrollRepository->findById($id);

        $merged = array_merge([
            'base_salary' => (float) $payroll->base_salary,
            'ot_amount' => (float) $payroll->ot_amount,
            'bonus' => (float) $payroll->bonus,
            'deductions' => (float) $payroll->deductions,
        ], $data);

        $merged['net_salary'] = round(max(0, $merged['base_salary'] + $merged['ot_amount'] + $merged['bonus'] - $merged['deductions']), 2);

        return $this->mapPayroll($this->payrollRepository->update($id, $merged));
    }

    public function markAsPaid(int $id, ?string $paymentDate = null)
    {
        return $this->mapPayroll($this->payrollRepository->update($id, [
            'payment_status' => 'Paid',
            'payment_date' => $paymentDate ?? now()->toDateString(),
        ]));
    }

    public function deletePayroll(int $id)
    {
        return $this->payrollRepository->delete($id);
    }

    /**
     * Per-employee calculation from the month's attendance records:
     * - Hourly Rate  = base_salary / 26 / 8
     * - OT Amount    = total OT hours * (Hourly Rate * 1.5)
     * - Late Penalty = late days * flat rate (rolled into deductions)
     * - Net Salary   = base_salary + ot_amount + bonus - deductions
     */
    private function calculateForUser($user, string $payrollType, string $monthYear, ?string $startDate, ?string $endDate)
    {
        $baseSalary = (float) ($user->salary ?? 0);

        $query = Attendance::where('user_id', $user->user_id);
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } else {
            $query->whereRaw("to_char(date, 'YYYY-MM') = ?", [$monthYear]);
        }
        $records = $query->get();

        $totalPresentDays = $records->whereIn('status', ['Present', 'Late'])->count();
        $totalAbsentDays = $records->where('status', 'Absent')->count();
        $lateDays = $records->where('status', 'Late')->count();
        $totalOtHours = round($records->sum(fn ($r) => max(0, (float) $r->total_hours - self::STANDARD_HOURS)), 2);

        $hourlyRate = $baseSalary > 0 ? round($baseSalary / self::WORKING_DAYS / self::STANDARD_HOURS, 4) : 0;
        $otAmount = round($totalOtHours * $hourlyRate * self::OT_MULTIPLIER, 2);
        $deductions = round($lateDays * self::LATE_FLAT_RATE, 2);
        $netSalary = round(max(0, $baseSalary + $otAmount - $deductions), 2);

        $record = [
            'user_id' => $user->user_id,
            'month_year' => $monthYear,
            'base_salary' => $baseSalary,
            'total_present_days' => $totalPresentDays,
            'total_absent_days' => $totalAbsentDays,
            'total_late_days' => $lateDays,
            'total_ot_hours' => $totalOtHours,
            'hourly_rate' => $hourlyRate,
            'ot_amount' => $otAmount,
            'bonus' => 0,
            'deductions' => $deductions,
            'net_salary' => $netSalary,
            'payment_status' => 'Pending',
            'user' => [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'role_id' => $user->role_id,
                'role' => $user->role ? [
                    'role_id' => $user->role->role_id,
                    'role_name' => $user->role->role_name,
                    'role_name_kh' => $user->role->role_name_kh,
                ] : null,
            ],
        ];

        // Uppercase aliases for the frontend contract
        $record['User_ID'] = $record['user_id'];
        $record['Month_Year'] = $record['month_year'];
        $record['Payroll_Type'] = $payrollType;
        $record['Base_Salary'] = $record['base_salary'];
        $record['Total_Present_Days'] = $record['total_present_days'];
        $record['Total_Absent_Days'] = $record['total_absent_days'];
        $record['Total_OT_Hours'] = $record['total_ot_hours'];
        $record['Overtime_Pay'] = $record['ot_amount'];
        $record['Hourly_OT_Rate'] = $hourlyRate;
        $record['Bonuses_Tips'] = 0;
        $record['Deductions'] = $record['deductions'];
        $record['Net_Salary'] = $record['net_salary'];
        $record['Payment_Status'] = 'Pending';

        return $record;
    }

    /**
     * Return the payroll with BOTH lowercase (database) and Uppercase
     * (legacy frontend contract) field names.
     */
    private function mapPayroll($payroll)
    {
        $array = $payroll->toArray();

        return array_merge($array, [
            'Payroll_ID' => (int) $payroll->payroll_id,
            'User_ID' => (int) $payroll->user_id,
            'Month_Year' => $payroll->month_year,
            'Payroll_Type' => 'Monthly',
            'Base_Salary' => (float) $payroll->base_salary,
            'Total_Present_Days' => (int) $payroll->total_present_days,
            'Total_Absent_Days' => (int) $payroll->total_absent_days,
            'Total_OT_Hours' => (float) $payroll->total_ot_hours,
            'Overtime_Pay' => (float) $payroll->ot_amount,
            'Hourly_OT_Rate' => (float) $payroll->base_salary > 0
                ? round($payroll->base_salary / self::WORKING_DAYS / self::STANDARD_HOURS, 4)
                : 0,
            'Bonuses_Tips' => (float) $payroll->bonus,
            'Manual_Bonus' => 0,
            'Deductions' => (float) $payroll->deductions,
            'Late_Penalties' => 0,
            'Manual_Deduction' => 0,
            'Net_Salary' => (float) $payroll->net_salary,
            'Payment_Status' => $payroll->payment_status,
            'Payment_Date' => $payroll->payment_date?->toDateString(),
        ]);
    }
}