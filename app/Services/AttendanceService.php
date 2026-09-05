<?php

namespace App\Services;

use App\Repositories\Contracts\AttendanceRepositoryInterface;

class AttendanceService
{
    /**
     * Standard work-day cut-off: clock-ins after this time are marked "Late".
     */
    private const LATE_AFTER = '08:30:00';

    protected $attendanceRepository;

    public function __construct(AttendanceRepositoryInterface $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    public function getAllAttendances(array $filters = [])
    {
        return $this->attendanceRepository->getAll($filters);
    }

    /**
     * Every active employee with their attendance for a specific date.
     */
    public function getDailyRoster(string $date, array $filters = [])
    {
        return $this->attendanceRepository->getDailyRoster($date, $filters);
    }

    public function getAttendanceById(int $id)
    {
        return $this->attendanceRepository->findById($id);
    }

    /**
     * Clock status for the authenticated user's current day.
     */
    public function getTodayStatus(int $userId)
    {
        $record = $this->attendanceRepository->findByUserAndDate($userId, now()->toDateString());

        if (!$record) {
            return [
                'status' => 'not_clocked_in',
                'can_clock_in' => true,
                'can_clock_out' => false,
                'record' => null,
            ];
        }

        if ($record->clock_out) {
            return [
                'status' => 'completed',
                'can_clock_in' => false,
                'can_clock_out' => false,
                'record' => $record,
            ];
        }

        return [
            'status' => 'clocked_in',
            'can_clock_in' => false,
            'can_clock_out' => true,
            'record' => $record,
        ];
    }

    /**
     * Clock the user in for today (one record per user per day).
     */
    public function clockIn(int $userId, array $data = [])
    {
        $date = $data['date'] ?? now()->toDateString();

        $existing = $this->attendanceRepository->findByUserAndDate($userId, $date);
        if ($existing) {
            if ($existing->clock_out) {
                throw new \Exception('You have already completed attendance for today', 409);
            }
            throw new \Exception('You have already clocked in today', 409);
        }

        $clockIn = now()->format('H:i:s');

        return $this->attendanceRepository->create([
            'user_id' => $userId,
            'date' => $date,
            'clock_in' => $clockIn,
            'status' => $clockIn > self::LATE_AFTER ? 'Late' : 'Present',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Clock the user out for today and calculate total hours.
     */
    public function clockOut(int $userId)
    {
        $attendance = $this->attendanceRepository->findByUserAndDate($userId, now()->toDateString());

        if (!$attendance) {
            throw new \Exception('No clock-in found for today. Please clock in first.', 404);
        }

        if ($attendance->clock_out) {
            throw new \Exception('You have already clocked out today', 409);
        }

        $clockOut = now()->format('H:i:s');

        return $this->attendanceRepository->update($attendance->attendance_id, [
            'clock_out' => $clockOut,
            'total_hours' => $this->computeHours($attendance->clock_in, $clockOut),
        ]);
    }

    public function createAttendance(array $data)
    {
        $existing = $this->attendanceRepository->findByUserAndDate((int) $data['user_id'], $data['date']);
        if ($existing) {
            throw new \Exception('Attendance already exists for this employee on this date', 409);
        }

        $data = $this->normalize($data);

        if (empty($data['status'])) {
            $data['status'] = (!empty($data['clock_in']) && $data['clock_in'] > self::LATE_AFTER) ? 'Late' : 'Present';
        }

        if (!empty($data['clock_in']) && !empty($data['clock_out'])) {
            $data['total_hours'] = $this->computeHours($data['clock_in'], $data['clock_out']);
        }

        return $this->attendanceRepository->create($data);
    }

    public function updateAttendance(int $id, array $data)
    {
        $attendance = $this->attendanceRepository->findById($id);

        $data = $this->normalize($data);

        $clockIn = $data['clock_in'] ?? $attendance->clock_in;
        $clockOut = $data['clock_out'] ?? $attendance->clock_out;

        if ($clockIn && $clockOut) {
            $data['total_hours'] = $this->computeHours($clockIn, $clockOut);
        }

        return $this->attendanceRepository->update($id, $data);
    }

    public function deleteAttendance(int $id)
    {
        return $this->attendanceRepository->delete($id);
    }

    /**
     * TESTING UTILITY: remove the authenticated user's attendance record
     * for today so the clock-in/clock-out flow can be tested repeatedly.
     */
    public function resetToday(int $userId)
    {
        $record = $this->attendanceRepository->findByUserAndDate($userId, now()->toDateString());

        if (!$record) {
            throw new \Exception('No attendance record found for today', 404);
        }

        $this->attendanceRepository->delete($record->attendance_id);
        return true;
    }

    /**
     * Normalize HH:MM to HH:MM:SS for time columns.
     */
    private function normalize(array $data): array
    {
        foreach (['clock_in', 'clock_out'] as $field) {
            if (!empty($data[$field]) && strlen($data[$field]) === 5) {
                $data[$field] = $data[$field] . ':00';
            }
        }
        return $data;
    }

    /**
     * Hours between clock_in and clock_out (overnight shifts roll past midnight).
     */
    private function computeHours($clockIn, $clockOut): float
    {
        $start = strtotime($clockIn);
        $end = strtotime($clockOut);

        if ($end <= $start) {
            $end += 86400; // overnight shift
        }

        return round(($end - $start) / 3600, 2);
    }
}