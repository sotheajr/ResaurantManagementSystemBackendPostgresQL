<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Services\AttendanceService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use ApiResponseTrait;

    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Attendance history.
     * Admins can view everyone (optionally filtered); other roles are
     * forcibly scoped to their own records.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $filters = $request->only(['user_id', 'month_year', 'date_from', 'date_to', 'status']);

            $isAdmin = (int) $user->role_id === 1;

            // Daily roster mode: an explicit "date", or the DEFAULT view (no
            // history filters) shows EVERY active employee — status
            // "Not Clocked In" when no record exists. Selected date defaults
            // to today.
            $hasHistoryFilters = $request->filled('month_year')
                || $request->filled('date_from')
                || $request->filled('date_to')
                || $request->filled('status');

            if (!$hasHistoryFilters) {
                $selectedDate = $request->input('date', \Carbon\Carbon::today()->toDateString());

                $rosterFilters = [];
                if (!$isAdmin) {
                    $rosterFilters['user_id'] = $user->user_id;
                } elseif (!empty($filters['user_id'])) {
                    $rosterFilters['user_id'] = $filters['user_id'];
                }
                return $this->successResponse(
                    $this->attendanceService->getDailyRoster($selectedDate, $rosterFilters)
                );
            }

            if (!$isAdmin) {
                $filters['user_id'] = $user->user_id;
            }

            return $this->successResponse($this->attendanceService->getAllAttendances($filters));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Today's clock status for the authenticated user.
     */
    public function todayStatus(Request $request)
    {
        try {
            $user = $request->user();
            return $this->successResponse($this->attendanceService->getTodayStatus((int) $user->user_id));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Clock in for today (self-service, any authenticated user).
     */
    public function clockIn(Request $request)
    {
        try {
            $user = $request->user();
            $attendance = $this->attendanceService->clockIn((int) $user->user_id, $request->only(['date', 'notes']));
            return $this->successResponse($attendance, 'Clocked in successfully', 201);
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            return $this->errorResponse($e->getMessage(), $code >= 400 && $code < 600 ? $code : 500);
        }
    }

    /**
     * Clock out for today (self-service, any authenticated user).
     */
    public function clockOut(Request $request)
    {
        try {
            $user = $request->user();
            $attendance = $this->attendanceService->clockOut((int) $user->user_id);
            return $this->successResponse($attendance, 'Clocked out successfully');
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            return $this->errorResponse($e->getMessage(), $code >= 400 && $code < 600 ? $code : 500);
        }
    }

    /**
     * TESTING UTILITY: reset today's attendance for the authenticated user.
     */
    public function resetToday(Request $request)
    {
        try {
            $user = $request->user();
            $this->attendanceService->resetToday((int) $user->user_id);
            return $this->successResponse(null, "Today's attendance has been reset for testing");
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            return $this->errorResponse($e->getMessage(), $code >= 400 && $code < 600 ? $code : 500);
        }
    }

    public function store(StoreAttendanceRequest $request)
    {
        try {
            $attendance = $this->attendanceService->createAttendance($request->validated());
            return $this->successResponse($attendance, 'Attendance logged successfully', 201);
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            return $this->errorResponse($e->getMessage(), $code >= 400 && $code < 600 ? $code : 500);
        }
    }

    public function show($id)
    {
        try {
            return $this->successResponse($this->attendanceService->getAttendanceById((int) $id));
        } catch (\Exception $e) {
            return $this->errorResponse('Attendance record not found', 404);
        }
    }

    public function update(UpdateAttendanceRequest $request, $id)
    {
        try {
            $attendance = $this->attendanceService->updateAttendance((int) $id, $request->validated());
            return $this->successResponse($attendance, 'Attendance updated successfully');
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            return $this->errorResponse($e->getMessage(), $code >= 400 && $code < 600 ? $code : 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->attendanceService->deleteAttendance((int) $id);
            return $this->successResponse(null, 'Attendance record deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Attendance record not found', 404);
        }
    }
}