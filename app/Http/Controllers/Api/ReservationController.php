<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use App\Services\ReservationService;
use App\Traits\ApiResponseTrait;

class ReservationController extends Controller
{
    use ApiResponseTrait;

    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function index()
    {
        try {
            $reservations = $this->reservationService->getAllReservations();
            return $this->successResponse($reservations);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StoreReservationRequest $request)
    {
        try {
            $reservation = $this->reservationService->createReservation($request->validated());
            return $this->successResponse($reservation, 'Reservation created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $reservation = $this->reservationService->getReservationById((int) $id);
            return $this->successResponse($reservation);
        } catch (\Exception $e) {
            return $this->errorResponse('Reservation not found', 404);
        }
    }

    public function update(UpdateReservationRequest $request, $id)
    {
        try {
            $reservation = $this->reservationService->updateReservation((int) $id, $request->validated());
            return $this->successResponse($reservation, 'Reservation updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->reservationService->deleteReservation((int) $id);
            return $this->successResponse(null, 'Reservation deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Reservation not found', 404);
        }
    }
}