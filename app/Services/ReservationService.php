<?php

namespace App\Services;

use App\Repositories\Contracts\ReservationRepositoryInterface;

class ReservationService
{
    protected $reservationRepository;

    public function __construct(ReservationRepositoryInterface $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    public function getAllReservations()
    {
        return $this->reservationRepository->getAll();
    }

    public function getReservationById(int $id)
    {
        return $this->reservationRepository->findById($id);
    }

    public function createReservation(array $data)
    {
        return $this->reservationRepository->create($data);
    }

    public function updateReservation(int $id, array $data)
    {
        return $this->reservationRepository->update($id, $data);
    }

    public function deleteReservation(int $id)
    {
        return $this->reservationRepository->delete($id);
    }
}