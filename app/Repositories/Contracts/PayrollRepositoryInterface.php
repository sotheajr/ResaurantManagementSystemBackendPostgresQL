<?php

namespace App\Repositories\Contracts;

interface PayrollRepositoryInterface
{
    public function getAll(array $filters = []);
    public function findById(int $id);
    public function findByUserAndMonth(int $userId, string $monthYear);
    public function updateOrCreate(array $attributes, array $values);
    public function update(int $id, array $data);
    public function delete(int $id);
}