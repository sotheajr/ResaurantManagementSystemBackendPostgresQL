<?php

namespace App\Repositories\Eloquent;

use App\Models\Attendance;
use App\Models\User;
use App\Repositories\Contracts\AttendanceRepositoryInterface;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function getAll(array $filters = [])
    {
        $query = Attendance::with([
            'user:user_id,username,full_name,role_id',
            'user.role:role_id,role_name,role_name_kh',
        ])
            ->orderBy('date', 'desc')
            ->orderBy('attendance_id', 'desc');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }
        if (!empty($filters['month_year'])) {
            // PostgreSQL: filter by "YYYY-MM" month
            $query->whereRaw("to_char(date, 'YYYY-MM') = ?", [$filters['month_year']]);
        }
        if (!empty($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }

    /**
     * Daily roster: every ACTIVE employee with their attendance record for the
     * given date. Users without a record appear with status "Not Clocked In".
     */
    public function getDailyRoster(string $date, array $filters = [])
    {
        $query = User::with([
            'role:role_id,role_name,role_name_kh',
            'attendances' => function ($q) use ($date) {
                $q->where('date', $date);
            },
        ])
            ->where('status', 'Active')
            ->orderBy('role_id')
            ->orderBy('user_id');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        $users = $query->get();

        return $users->map(function ($user) use ($date) {
            $attendance = $user->attendances->first();

            return [
                'attendance_id' => $attendance?->attendance_id,
                'user_id' => $user->user_id,
                'date' => $date,
                'clock_in' => $attendance?->clock_in,
                'clock_out' => $attendance?->clock_out,
                'total_hours' => $attendance?->total_hours ?? 0,
                'work_hours' => $attendance ? (float) $attendance->total_hours : 0,
                'ot_hours' => $attendance ? max(0, round((float) $attendance->total_hours - 8, 2)) : 0,
                'status' => $attendance?->status ?? 'Not Clocked In',
                'notes' => $attendance?->notes,
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
        })->values();
    }

    public function findById(int $id)
    {
        return Attendance::with([
            'user:user_id,username,full_name,role_id',
            'user.role:role_id,role_name,role_name_kh',
        ])->findOrFail($id);
    }

    public function findByUserAndDate(int $userId, string $date)
    {
        return Attendance::where('user_id', $userId)->where('date', $date)->first();
    }

    public function create(array $data)
    {
        return Attendance::create($data);
    }

    public function update(int $id, array $data)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update($data);
        return $attendance->fresh([
            'user:user_id,username,full_name,role_id',
            'user.role:role_id,role_name,role_name_kh',
        ]);
    }

    public function delete(int $id)
    {
        $attendance = Attendance::findOrFail($id);
        return $attendance->delete();
    }
}