<?php

namespace App\Repositories\Eloquent;

use App\Models\Reservation;
use App\Repositories\Contracts\ReservationRepositoryInterface;

class ReservationRepository implements ReservationRepositoryInterface
{
    public function getAll()
    {
        return Reservation::with(['customer', 'table'])->orderBy('reservation_date', 'desc')->get();
    }

    public function findById(int $id)
    {
        return Reservation::with(['customer', 'table'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Reservation::create($data);
    }

    public function update(int $id, array $data)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update($data);
        return $reservation->fresh();
    }

    public function delete(int $id)
    {
        $reservation = Reservation::findOrFail($id);
        return $reservation->delete();
    }
}