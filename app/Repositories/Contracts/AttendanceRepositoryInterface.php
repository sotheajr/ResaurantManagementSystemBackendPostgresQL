<?php

namespace App\Repositories\Contracts;

interface AttendanceRepositoryInterface
{
    public function getAll(array $filters = []);
    public function getDailyRoster(string $date, array $filters = []);
    public function findById(int $id);
    public function findByUserAndDate(int $userId, string $date);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}