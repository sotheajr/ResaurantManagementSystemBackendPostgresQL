<?php

namespace App\Repositories\Eloquent;

use App\Models\Payroll;
use App\Repositories\Contracts\PayrollRepositoryInterface;

class PayrollRepository implements PayrollRepositoryInterface
{
    public function getAll(array $filters = [])
    {
        $query = Payroll::with([
            'user:user_id,username,full_name,role_id,salary',
            'user.role:role_id,role_name,role_name_kh',
        ])->orderBy('payroll_id', 'desc');

        if (!empty($filters['month_year'])) {
            $query->where('month_year', $filters['month_year']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        return $query->get();
    }

    public function findById(int $id)
    {
        return Payroll::with([
            'user:user_id,username,full_name,role_id,salary',
            'user.role:role_id,role_name,role_name_kh',
        ])->findOrFail($id);
    }

    public function findByUserAndMonth(int $userId, string $monthYear)
    {
        return Payroll::where('user_id', $userId)->where('month_year', $monthYear)->first();
    }

    public function updateOrCreate(array $attributes, array $values)
    {
        return Payroll::updateOrCreate($attributes, $values);
    }

    public function update(int $id, array $data)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->update($data);
        return $payroll->fresh([
            'user:user_id,username,full_name,role_id,salary',
            'user.role:role_id,role_name,role_name_kh',
        ]);
    }

    public function delete(int $id)
    {
        $payroll = Payroll::findOrFail($id);
        return $payroll->delete();
    }
}